<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageFile;
use App\Models\MessageStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $title = 'Messages';
        if ($request->has('t')) {
            session(['message_type' => $request->get('t')]);
        }
        return view('message.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $data = [];
        $data['senderId'] = $request->input('senderId');
        $data['receiverId'] = $request->input('receiverId');
        $data['productId'] = $request->input('productId');
        $data['subject'] = $request->input('subject');
        $data['message'] = $request->input('message');

        $legalName = '';

        if ($request->filled('productId')) {
            $data['product']   = Product::findOrFail($data['productId']);
        }

        /***:- receiver info  -:***/
        if ($request->filled('receiverId')) {
            $data['receiverIdInfo']   = User::with(['vendor', 'buyer'])->findOrFail($data['receiverId']);
        }

        $message_html = view('message.message-popup', compact('data'))->render();
        return response()->json([
            'status' => true,
            'message' => 'Vendor Product found.',
            'messageText' => '',
            'message_html' => $message_html
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeMessageData(Request $request, $draft = null)
    {
        try {
            $message_type = 'raprocure';
            $type = 'compose_email';
            $email_label_id = 5;
            $receiver_id = $request->receiverId;
            $sender_id = $request->senderId;
            $subject = $request->subject;
            $message = $request->message;

            $receiverUser = User::find($receiver_id);
            $type_message =  $receiverUser->user_type == 1 || $receiverUser->user_type == 2 ? 2 : 3;

            $message = Message::create([
                'subject' => $subject,
                'message' => $message,
                'email_label_id' =>  $email_label_id,
                'type_message' => $type_message,
                'type' => 'compose_email',
                'parent_id' => null,
            ]);

            if ($request->hasFile('attachment')) {
                $attachment = $request->file('attachment');
                $fileName = time() . '.' . $attachment->getClientOriginalExtension();
                $filePath = $attachment->storeAs('attachments', $fileName, ['disk' => 'public_uploads']);

                MessageFile::create([
                    'message_id' => $message->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                ]);
            }
            // Save the status of the message to the database
            MessageStatus::create([
                'message_id' => $message->id,
                'sender_id' => $sender_id,
                'receiver_id' =>  $receiver_id,
                'sender_send_status' => '2',
                'receiver_inbox_status' => '2',
                'sender_draft_status' => $draft
            ]);

            /***:- update for parent_id  -:***/
            $message = Message::where('id', $message->id)->update([
                'parent_id' => $message->id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully.'
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}