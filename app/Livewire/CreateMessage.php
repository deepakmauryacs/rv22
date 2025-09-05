<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\MessageFavourite;
use App\Models\MessageFile;
use App\Models\MessageStatus;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class CreateMessage extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $id, $subject, $message, $attachment, $receiver_id, $sender_id,  $email_label_id, $type, $parent_id;

    public $replyMessageText = '';

    public $selectAllFavourite = false;

    public $type_message = 3;
    public $message_type = 'raprocure';

    public $draftEditMode = false;
    public $listing_type = 'inbox';

    public $search = '';
    public $page = 1;
    public $perPage = 50;

    public $inboxUnreadCount = 0;
    public $draftUnreadCount = 0;
    public $trashUnreadCount = 0;

    public $replyListStatus = false;
    public $hasMoreMessages = true;

    public $selectAll = false;
    public $selectedMessages = [];

    public $uploadedFilePath = '';


    protected $queryString = [];
    /*protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];*/


    protected $listeners = [
        'set-message' => 'setMessage'
    ];


    public function setMessage($content)
    {
        // $this->message = $payload['content'] ?? '';
        $this->message = $content ?? '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function loadMore()
    {

        if ($this->hasMoreMessages) {
            $this->perPage += 50;
        } else {
            //$this->dispatch('no-more-items');
        }
    }


    protected $rules = [
        'subject' => 'min:3',
        'message' => 'min:1',
        'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,rar,ppt,txt,gif,pptx,doc,docx,xls,zip,xlsx,png|max:5048', // 2MB
    ];

    public function mount(Message $msg)
    {


        $this->subject = $msg->subject ?? '';
        $this->message = html_entity_decode($msg->message) ?? '';
        $this->id = $msg->id;
        $this->message_type = session('message_type'); //??(Auth::user()->user_type == 3) ? 'buyer' : 'raprocure';
        $this->page = 1;
        $this->search = '';
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function composeMessage(Request $request)
    {

        $validated = $this->validate();

        // Save the message to the database
        $this->saveData();
        $this->listing_type = 'send';
        session()->flash('success', 'Your message has been sent!');
        // $this->resetInputFields();
        $this->dispatch('hide-compose-modal');
    }

    public function replyMessage(Request $request)
    {
        // dd($request->all());
        if ($this->message != '') {
            $this->saveData();
            $this->reset('message', 'attachment');
        }
        $this->dispatch('scroll-to-bottom');
    }

    public function saveData($draft = null)
    {
        try {
            $receiverId = $this->receiver_id;

            if ($this->receiver_id) {
                $receiverId = Auth::user()->id === $this->sender_id ? $this->receiver_id : $this->sender_id;
            } else {
                $receiverId = 2;
            }

            //dd($receiverId);
            if (((Auth::user()->user_type == '1' || Auth::user()->user_type == '2') && $this->message_type == 'raprocure') || Auth::user()->user_type == '3' && ($this->message_type == 'vendor' || $this->message_type == 'buyer')) {
                //$receiverId = $this->receiver_id ? $this->receiver_id : 2;
                $this->type = $this->replyListStatus ? 'reply' : 'compose_email';
                $this->type_message = 3;
            } else {
                if ($this->replyListStatus) {
                    $this->type = 'reply';
                    $this->type_message = 2;
                }
            }


            // dd($this->type_message, $this->type, $this->parent_id);
            $message = Message::create([
                'subject' => $this->subject,
                'message' => $this->message,
                'email_label_id' => ($this->email_label_id != '') ? $this->email_label_id : 5,
                'type_message' => $this->type_message,
                'type' => $this->type,
                'parent_id' => $this->parent_id != '' ? $this->parent_id : null,
            ]);

            if ($this->attachment) {
                $fileName = time() . '.' . $this->attachment->getClientOriginalExtension();
                $filePath = $this->attachment->storeAs('attachments', $fileName, ['disk' => 'public_uploads']);

                MessageFile::create([
                    'message_id' => $message->id,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                ]);
            }
            // Save the status of the message to the database
            MessageStatus::create([
                'message_id' => $message->id,
                'sender_id' => Auth::user()->id,
                'receiver_id' =>  $receiverId,
                'sender_send_status' => '2',
                'receiver_inbox_status' => '2',
                'sender_draft_status' => $draft
            ]);


            /***:- update for parent_id  -:***/
            if (!$this->parent_id) {
                $message = Message::where('id', $message->id)->update([
                    'parent_id' => $message->id
                ]);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            throw $e;
        }
    }


    #[On('save-draft')]
    public function saveAsDraft()
    {
        if ($this->subject && $this->message) {
            $this->type = 'compose_email';
            // Save the message to the database
            if ($this->id != '') {
                $this->updateDraftData(2);
            } else {
                $this->saveData(2);
            }
            // $this->saveData(2);

            session()->flash('success', 'Your message has been saved to draft!');
        } else {
            session()->flash('error', 'Message not saved to draft. Please fill in all required fields!');
            // return;
        }
        $this->resetInputFields();
    }



    public function editDraftData($id)
    {

        $msg = Message::with(['messageStatus' => function ($q) {
            $q->where('sender_id', Auth::user()->id)->whereNotNull('sender_draft_status');
            if ($this->receiver_id) {
                $q->where('receiver_id', $this->receiver_id);
            }
        }, 'messageFile'])->findOrFail($id);

        if (!$msg) {
            session()->flash('error', 'Message not found!');
            return;
        }

        // dd($msg->messageFile->file_path);
        $this->uploadedFilePath = $msg->messageFile->file_path ?? null;
        $this->draftEditMode = true;
        $this->id = $id;
        $this->subject = $msg->subject;
        $this->message = html_entity_decode($msg->message);

        $this->dispatch('show-compose-modal');
    }

    public function replyMessageModal()
    {
        // $this->subject = null;
        $this->message = null;
        $this->subject = $this->replyMessageText;
        $this->dispatch('show-compose-modal');
        $this->resetErrorBag();
    }
    public function showComposeModal()
    {
        $this->uploadedFilePath = null;
        $this->subject = null;
        $this->message = null;
        $this->dispatch('show-compose-modal');
        $this->resetErrorBag();
    }

    public function messageType($messageType)
    {
        $this->id = null;
        $this->parent_id = null;
        $this->message = null;
        $this->subject = null;
        $this->message_type = $messageType;
        $this->replyListStatus = false;
        $this->uploadedFilePath = null;
        $this->search = null;
        $this->resetPage();
        //$this->dispatch('show-inbox');
    }

    public function listingType($listingType)
    {
        $this->id = null;
        $this->parent_id = null;
        $this->listing_type = $listingType;
        $this->replyListStatus = false;
        $this->search = null;
        $this->page = null;
        $this->selectAll = false;
        $this->resetPage();
        $this->dispatch('reset-row-checkboxes');
    }





    public function sendDraft()
    {
        $this->validate();
        // Save the message to the database
        $this->updateDraftData();

        session()->flash('success', 'Your draft message has been sent!');
        $this->resetInputFields();
        $this->dispatch('hide-compose-modal');
    }

    public function updateDraftData($sender_draft_status = null)
    {
        try {

            /***:- message update  -:***/
            $msg = Message::find($this->id);

            $msg->update([
                'subject' => $this->subject,
                'message' => $this->message,
            ]);

            /***:- message status update  -:***/
            $msgStatus = MessageStatus::where('message_id', $this->id)->where('sender_id', Auth::user()->id)->first();
            $msgStatus->update([
                'sender_draft_status' => $sender_draft_status,
            ]);


            /***:- update message file if changed  -:***/
            if ($this->attachment) {
                $fileName = time() . '.' . $this->attachment->getClientOriginalExtension();
                $filePath = $this->attachment->storeAs('attachments', $fileName, ['disk' => 'public_uploads']);

                MessageFile::updateOrCreate(
                    ['message_id' => $this->id],
                    [
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                    ]
                );
            }
            $this->listing_type = 'send';
            session()->flash('success', 'Your message has been sent!');
            $this->resetInputFields();
            $this->dispatch('hide-compose-modal');
        } catch (\Throwable $th) {
            logger()->error($th);
        }
    }

    #[On('resetInputFields')]
    public function resetInputFields()
    {
        $this->reset('subject', 'message', 'attachment', 'receiver_id', 'type_message');
        $this->uploadedFilePath = null;
        //$this->dispatch('$refresh');
        $this->dispatch('hide-compose-modal');
    }


    public function replyBack()
    {
        $this->replyListStatus = false;
        $this->id = null;
        $this->parent_id = null;
        $this->search = null;
        $this->page = null;
        $this->listing_type = 'inbox';
        $this->resetInputFields();
    }

    public function render()
    {
        /***:- inbox  -:***/

        $listingData  = [];
        if ($this->parent_id) {
            $listingData = $this->replyListing();
        } else {
            $listingData = $this->msgListing();
        }

        $msgCount = $this->msgCount();

        // dd($listingData);


        return view('livewire.message-layout', compact('listingData', 'msgCount'));
    }

    public function msgListing()
    {

        $listingData  = [];
        $auth_id = Auth::user()->id;

        if ((Auth::user()->user_type == 2 && $this->message_type == 'buyer') || (Auth::user()->user_type == 1 && $this->message_type == 'vendor')) {
            $this->type_message = 2;
        } else {
            $this->type_message = 3;
        }

        try {
            $inbox  = DB::table('messages')
                ->join('message_statuses', 'messages.id', '=', 'message_statuses.message_id')
                ->join('users as sender_user', 'sender_user.id', '=', 'message_statuses.sender_id')
                ->join('users as receiver_user', 'receiver_user.id', '=', 'message_statuses.receiver_id');

            $rawQuery = "messages.parent_id, (messages.id) as id, messages.type, messages.subject, messages.message, messages.email_label_id, messages.last_ip, messages.status, messages.type_message,messages.created_at, message_statuses.id as message_statuses_id,message_statuses.message_id, message_statuses.sender_id, message_statuses.receiver_id, message_statuses.sender_send_status, message_statuses.receiver_inbox_status, message_statuses.sender_draft_status, message_statuses.sender_trash_status, message_statuses.receiver_trash_status,message_statuses.updated_at";


            $inbox->where('messages.type_message', $this->type_message);

            $rawQuery = "$rawQuery, sender_user.name as sender_name, sender_user.email as sender_email, sender_user.user_type as sender_user_type, receiver_user.name as receiver_name, receiver_user.email as receiver_email, receiver_user.user_type as receiver_user_type";

            if ($this->listing_type == 'inbox') {
                $inbox->where('message_statuses.receiver_id', $auth_id)
                    ->whereNull('message_statuses.sender_draft_status')
                    ->whereNull('message_statuses.receiver_trash_status');
                if ($this->message_type == 'buyer') {
                    $inbox->leftJoin('buyers', 'buyers.user_id', '=', 'message_statuses.sender_id');
                    $inbox->where('sender_user.user_type', 1);
                    $rawQuery = "$rawQuery, buyers.legal_name as buyer_legal_name, buyers.buyer_code as buyer_code";

                    /***:- buyer unread count  -:***/
                } elseif ($this->message_type == 'vendor') {
                    $inbox->leftJoin('vendors', 'vendors.user_id', '=', 'message_statuses.sender_id');
                    $inbox->where('sender_user.user_type', 2);
                    $rawQuery = "$rawQuery, vendors.legal_name as vendor_legal_name, vendors.vendor_code as vendor_code ";
                } else {
                    $inbox->where('sender_user.user_type', 3);
                }

                $subQuery = clone $inbox;
                $subQuery->select(DB::raw('MAX(messages.id) as id'))
                    ->groupBy('messages.parent_id');

                $inbox->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('messages.id', '=', 'latest.id');
                });
            } elseif ($this->listing_type == 'send') {
                $inbox->where('message_statuses.sender_id', $auth_id)
                    ->whereNull('message_statuses.sender_draft_status')
                    ->whereNull('message_statuses.sender_trash_status');

                if ($this->message_type == 'buyer') {
                    $inbox->leftJoin('buyers', 'buyers.user_id', '=', 'message_statuses.receiver_id');
                    $inbox->where('receiver_user.user_type', 1);
                    $rawQuery = "$rawQuery, buyers.legal_name as buyer_legal_name, buyers.buyer_code as buyer_code ";
                } elseif ($this->message_type == 'vendor') {
                    $inbox->leftJoin('vendors', 'vendors.user_id', '=', 'message_statuses.receiver_id');
                    $inbox->where('receiver_user.user_type', 2);
                    $rawQuery = "$rawQuery, vendors.legal_name as vendor_legal_name, vendors.vendor_code as vendor_code ";
                } elseif ($this->message_type == 'raprocure') {
                    $inbox->where('receiver_user.user_type', 3);
                }

                $subQuery = clone $inbox;
                $subQuery->select(DB::raw('MAX(messages.id) as id'))
                    ->groupBy('messages.parent_id');

                $inbox->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('messages.id', '=', 'latest.id');
                });
            } elseif ($this->listing_type == 'draft') {
                $inbox
                    ->where('message_statuses.sender_id', $auth_id)
                    ->whereNotNull('message_statuses.sender_draft_status')
                    ->whereNull('message_statuses.sender_trash_status');
                if ($this->message_type == 'buyer') {
                    $inbox->leftJoin('buyers', 'buyers.user_id', '=', 'message_statuses.receiver_id');
                    $inbox->where('receiver_user.user_type', 1);
                    $rawQuery = "$rawQuery, buyers.legal_name as buyer_legal_name, buyers.buyer_code as buyer_code ";
                } elseif ($this->message_type == 'vendor') {
                    $inbox->leftJoin('vendors', 'vendors.user_id', '=', 'message_statuses.receiver_id');
                    $inbox->where('receiver_user.user_type', 2);
                    $rawQuery = "$rawQuery, vendors.legal_name as vendor_legal_name, vendors.vendor_code as vendor_code ";
                }

                $subQuery = clone $inbox;
                $subQuery->select(DB::raw('MAX(messages.id) as id'))
                    ->groupBy('messages.parent_id');

                $inbox->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('messages.id', '=', 'latest.id');
                });
            } elseif ($this->listing_type == 'trash') {
                $inbox->where(function ($query) use ($auth_id) {
                    $query->where(function ($query) use ($auth_id) {
                        $query->where('message_statuses.sender_id', $auth_id)
                            ->whereBetween('message_statuses.sender_trash_status', [1, 2]);
                    })
                        ->orWhere(function ($query) use ($auth_id) {
                            $query->where('message_statuses.receiver_id', $auth_id)
                                ->whereBetween('message_statuses.receiver_trash_status', [1, 2]);
                        });
                });
                $senderUserType = 0;
                $receiverUserType = 0;
                if ($this->message_type == 'buyer') {
                    $inbox->leftJoin('buyers', 'buyers.user_id', '=', 'message_statuses.receiver_id');;
                    if (Auth::user()->user_type == 3) {
                        $senderUserType = 1;
                        $receiverUserType = 3;
                    } elseif (Auth::user()->user_type == 2) {
                        $senderUserType = 1;
                        $receiverUserType = 2;
                    }
                    $rawQuery = "$rawQuery, buyers.legal_name as buyer_legal_name, buyers.buyer_code as buyer_code ";
                } elseif ($this->message_type == 'vendor') {
                    $inbox->leftJoin('vendors', 'vendors.user_id', '=', 'message_statuses.receiver_id');

                    if (Auth::user()->user_type == 3) {
                        $senderUserType = 2;
                        $receiverUserType = 3;
                    } elseif (Auth::user()->user_type == 1) {
                        $senderUserType = 1;
                        $receiverUserType = 2;
                    }

                    $rawQuery = "$rawQuery, vendors.legal_name as vendor_legal_name, vendors.vendor_code as vendor_code ";
                } elseif ($this->message_type == 'raprocure') {
                    if (Auth::user()->user_type == 2) {
                        $senderUserType = 2;
                        $receiverUserType = 3;
                    } elseif (Auth::user()->user_type == 1) {
                        $senderUserType = 1;
                        $receiverUserType = 3;
                    }
                }


                $inbox->where(function ($query) use ($senderUserType, $receiverUserType) {

                    $query->where(function ($query) use ($senderUserType, $receiverUserType) {
                        $query->where('sender_user.user_type', $senderUserType)
                            ->where('receiver_user.user_type', $receiverUserType);
                    })
                        ->orWhere(function ($query) use ($senderUserType, $receiverUserType) {
                            $query->where('sender_user.user_type', $receiverUserType)
                                ->where('receiver_user.user_type', $senderUserType);
                        });
                });

                $subQuery = clone $inbox;
                $subQuery->select(DB::raw('MAX(messages.id) as id'))
                    ->groupBy('messages.parent_id');

                $inbox->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('messages.id', '=', 'latest.id');
                });
            }

            if ($this->search && $this->search != '') {
                $inbox->where(function ($query) {
                    $query->where('messages.subject', 'like', '%' . $this->search . '%');
                    if ($this->listing_type == 'inbox') {
                        $query->orWhere('sender_user.name', 'like', '%' . $this->search . '%');
                    } elseif ($this->listing_type == 'send' || $this->listing_type == 'draft') {
                        $query->orWhere('receiver_user.name', 'like', '%' . $this->search . '%');
                    } elseif ($this->listing_type == 'trash') {
                        $query->orWhere('sender_user.name', 'like', '%' . $this->search . '%')
                            ->orWhere('receiver_user.name', 'like', '%' . $this->search . '%');
                    }
                });
            }

            $inbox->leftJoin('message_favourites as f1', function ($join) use ($auth_id) {
                $join->on('messages.id', '=', 'f1.message_id')
                    ->where('f1.user_id', '=', $auth_id);
            })->leftJoin('message_favourites as f2', function ($join) use ($auth_id) {
                $join->on('messages.parent_id', '=', 'f2.message_id')
                    ->where('f2.user_id', '=', $auth_id);
            });

            $rawQuery = "$rawQuery,  CASE WHEN f1.id IS NOT NULL THEN 1 WHEN f2.id IS NOT NULL THEN 1 ELSE 0 END as is_fav";

            if ($this->id) {
                $inbox->where('messages.parent_id', $this->id);
            }
            // $inbox->orderByDesc('is_fav');
            $inbox->orderBy('messages.id', 'desc');
            $inbox->selectRaw($rawQuery);
            //dd($inbox->toSql(), $inbox->getBindings());
            // $listingData = $inbox->paginate($this->perPage);
            $listingData = $inbox->get();

            return $listingData;
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function msgCount()
    {
        try {
            $inbox  = DB::table('messages')
                ->join('message_statuses', 'messages.id', '=', 'message_statuses.message_id')
                ->join('users as sender_user', 'sender_user.id', '=', 'message_statuses.sender_id')
                ->join('users as receiver_user', 'receiver_user.id', '=', 'message_statuses.receiver_id');

            $rawQuery = "sender_user.user_type, COUNT(CASE WHEN message_statuses.receiver_inbox_status = 2 THEN 1 END) as inbox_unread_count, COUNT(CASE WHEN message_statuses.receiver_inbox_status = 1 THEN 1 END) as inbox_read_count";


            $inbox->where(function ($q) {
                $q->where('message_statuses.receiver_id', Auth::user()->id)
                    ->whereNull('message_statuses.sender_draft_status')
                    ->whereNull('message_statuses.receiver_trash_status');
            });

            $subQuery = clone $inbox;
            $subQuery->select(DB::raw('MAX(messages.id) as id'))
                ->groupBy('messages.parent_id');

            $inbox->joinSub($subQuery, 'latest', function ($join) {
                $join->on('messages.id', '=', 'latest.id');
            });
            $inbox->groupBy('sender_user.user_type');
            $inbox->selectRaw($rawQuery);
            return $inbox->get();
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function markSelectedAsFavourite($isFav)
    {
        Log::info('markSelectedAsFavourite called with is_fav: ' . $isFav);

        try {
            if (empty($this->selectedMessages)) {
                session()->flash('error', 'No messages selected.');
                return;
            }

            foreach ($this->selectedMessages as $item) {
                $messages = json_decode(json_encode($item), true);
                $this->toggleFavourite($messages['parent_id'], $isFav);
            }

            // Update the state correctly
            $this->selectAllFavourite = (bool) $isFav;
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }

    public function toggleFavouriteSingle($messageId)
    {
        $userId = Auth::id();
        $msg = Message::find($messageId);

        if (!$msg) return;

        $messageFavourite = MessageFavourite::where('message_id', $msg->parent_id)
            ->where('user_id', $userId)
            ->first();

        if ($messageFavourite) {
            $messageFavourite->delete();
        } else {
            MessageFavourite::create([
                'message_id' => $msg->parent_id,
                'user_id' => $userId,
            ]);
        }
    }

    public function toggleAllFavourite()
    {
        $newState = !$this->selectAllFavourite; // flip current state
        $this->markSelectedAsFavourite($newState ? 1 : 0);
    }


    public function toggleFavourite($messageId, $isFav)
    {
        Log::info('Second data here, is_fav: ' . $isFav);

        try {
            $userId = Auth::user()->id;
            $msg = Message::find($messageId);

            if ($msg) {
                Log::info('Processing message ID: ' . $msg->parent_id);

                $messageFavourite = MessageFavourite::where('message_id', $msg->parent_id)
                    ->where('user_id', $userId)
                    ->first();

                if ($isFav) {
                    // If isFav == 1, we want to favorite it (create if not exists)
                    if (!$messageFavourite) {
                        Log::info('Creating favourite');
                        MessageFavourite::create([
                            'message_id' => $msg->parent_id,
                            'user_id' => $userId,
                        ]);
                    }
                } else {
                    // If isFav == 0, we want to unfavourite (delete if exists)
                    if ($messageFavourite) {
                        Log::info('Deleting favourite');
                        $messageFavourite->delete();
                    }
                }
            } else {
                session()->flash('error', 'Message not found.');
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }




    public function readSelectedOne($markType, $id, $message_id, $senderId, $receiverId)
    {
        try {
            $markType = $markType == 'read' ? 1 : 2;
            if (Auth::user()->id == $senderId) {
                $updateData = ['sender_send_status' => $markType];
            } elseif (Auth::user()->id == $receiverId) {
                $updateData = ['receiver_inbox_status' => $markType];
            }
            MessageStatus::where('id', $id)
                ->where('message_id', $message_id)
                ->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId)
                ->update($updateData);
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function markAsRead($markType)
    {
        try {
            foreach ($this->selectedMessages as $selected) {
                $data = is_array($selected) ? $selected : json_decode($selected, true);
                $msgData = MessageStatus::find($data['message_statuses_id']);
                $this->readSelectedOne($markType, $msgData->id, $msgData->message_id, $msgData->sender_id, $msgData->receiver_id);
            }
            session()->flash('message', "Selected message $markType Successfully");
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function deleteSelectedOne($parentId, $senderID, $receiverId)
    {
        $authID = Auth::user()->id;
        try {
            $allMessage = $this->getAllMessages($parentId, $senderID, $receiverId);

            if ($allMessage->count() > 0) {
                $deleteStatus = $this->listing_type == 'trash' ? 3 : 2;
                foreach ($allMessage as $key => $value) {
                    $messageStatus = MessageStatus::where('id', $value->id);
                    if ($value->sender_id == $authID) {
                        $messageStatus->where('sender_id', $authID)
                            ->update(['sender_trash_status' => $deleteStatus]);
                    } elseif ($value->receiver_id == $authID) {
                        $messageStatus->where('receiver_id', $authID)
                            ->update(['receiver_trash_status' => $deleteStatus]);
                    }
                }
                $this->selectedMessages = [];
                $this->selectAll = false;
                session()->flash('message', 'Message moved to trash.');
            } else {
                session()->flash('error', 'Message not found.');
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    /***:- get all sender message and receiver message by auth id  -:***/
    public function getAllMessages($parentId, $senderID, $receiverId)
    {
        try {
            $authId = Auth::user()->id;
            $allMessage =  DB::table('messages')
                ->join('message_statuses', 'messages.id', '=', 'message_statuses.message_id')
                ->where(function ($q) use ($authId, $senderID, $receiverId) {
                    $q->where(function ($query) use ($authId, $senderID, $receiverId) {
                        if ($authId == $senderID) {
                            $query->where('message_statuses.sender_id', $senderID)
                                ->where('message_statuses.receiver_id', $receiverId);
                        } else {
                            $query->where('message_statuses.sender_id', $receiverId)
                                ->where('message_statuses.receiver_id', $senderID);
                        }
                    })
                        ->orWhere(function ($query) use ($authId, $senderID, $receiverId) {
                            if ($authId == $senderID) {
                                $query->where('message_statuses.sender_id', $receiverId)
                                    ->where('message_statuses.receiver_id', $senderID);
                            } else {
                                $query->where('message_statuses.sender_id', $senderID)
                                    ->where('message_statuses.receiver_id', $receiverId);
                            }
                        });
                })
                ->where('messages.parent_id', $parentId)
                ->select('messages.parent_id', 'message_statuses.id', 'message_statuses.message_id', 'message_statuses.sender_id', 'message_statuses.receiver_id')

                ->get();
            return $allMessage;
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function deleteSelected()
    {
        if (!empty($this->selectedMessages)) {
            foreach ($this->selectedMessages as $data) {
                $this->deleteSelectedOne($data['parent_id'], $data['sender_id'], $data['receiver_id']);
            }
            $this->selectedMessages = [];
            $this->selectAll = false;
            session()->flash('message', 'Selected messages have been moved to trash.');
        }
    }

    public function replyListStatusType($msgParentId)
    {
        $this->replyListStatus = true;
        $this->id = $msgParentId;
        $msg = Message::with('messageStatus')->find($msgParentId);
        if ($msg) {
            $this->subject = $msg->subject;
            $this->replyMessageText = $msg->subject;

            $this->receiver_id = $msg->messageStatus[0]->receiver_id ?? null;
            $this->email_label_id = $msg->email_label_id ?? null;
            $this->type = 'reply';
            $this->parent_id = $msg->parent_id;
            $this->sender_id = $msg->messageStatus[0]->sender_id ?? null;
            $this->readSelectedOne('read', $msg->messageStatus[0]->id, $msg->messageStatus[0]->message_id, $msg->messageStatus[0]->sender_id, $msg->messageStatus[0]->receiver_id);
        }
        $this->search = null;
        $this->page = null;
        $this->attachment = null;
        $this->message = null;
        $this->resetErrorBag();
        $this->reset('attachment', 'message');
        $this->dispatch('scroll-to-bottom');
    }

    public function replyListing()
    {

        $listingData  = [];
        $auth_id = Auth::user()->id;
        if ((Auth::user()->user_type == 2 && $this->message_type == 'buyer') || (Auth::user()->user_type == 1 && $this->message_type == 'vendor')) {
            $this->type_message = 2;
        } else {
            $this->type_message = 3;
        }

        try {
            $inbox  = DB::table('messages')
                ->join('message_statuses', 'messages.id', '=', 'message_statuses.message_id')
                ->leftJoin('message_files', 'message_files.message_id', '=', 'message_statuses.message_id');
            $rawQuery = "messages.*, message_statuses.*, message_files.file_name,message_files.file_path";

            $inbox->where('messages.type_message', $this->type_message);

            $inbox->join('users as sender_user', 'sender_user.id', '=', 'message_statuses.sender_id')
                ->join('users as receiver_user', 'receiver_user.id', '=', 'message_statuses.receiver_id');

            // if ($this->message_type == 'buyer') {
            $inbox->leftJoin('buyers as buyer_sender', 'buyer_sender.user_id', '=', 'sender_user.id');
            $rawQuery = "$rawQuery, buyer_sender.user_id as buyer_sender_user_id,  buyer_sender.legal_name as buyer_sender_legal_name, buyer_sender.buyer_code as buyer_sender_buyer_code ";


            $inbox->leftJoin('buyers as buyer_receiver', 'buyer_receiver.user_id', '=', 'receiver_user.id');
            $rawQuery = "$rawQuery, buyer_receiver.user_id as buyer_receiver_user_id,  buyer_receiver.legal_name as buyer_receiver_legal_name, buyer_receiver.buyer_code as buyer_receiver_buyer_code ";
            // } elseif ($this->message_type == 'vendor') {

            $inbox->leftJoin('vendors as vender_sender', 'vender_sender.user_id', '=', 'sender_user.id');
            $rawQuery = "$rawQuery, vender_sender.user_id as vender_sender_user_id, vender_sender.legal_name as vender_sender_legal_name, vender_sender.vendor_code as vender_sender_vendor_code ";

            $inbox->leftJoin('vendors as vender_receiver', 'vender_receiver.user_id', '=', 'receiver_user.id');
            $rawQuery = "$rawQuery, vender_receiver.user_id as vender_receiver_user_id, vender_receiver.legal_name as vender_receiver_legal_name, vender_receiver.vendor_code as vender_receiver_vendor_code ";
            // }


            $rawQuery = "$rawQuery, sender_user.id as sender_id, sender_user.name as sender_name, sender_user.email as sender_email, sender_user.user_type as sender_user_type, receiver_user.id as receiver_id, receiver_user.name as receiver_name, receiver_user.email as receiver_email, receiver_user.user_type as receiver_user_type";

            // dd($inbox->selectRaw($rawQuery)->get());

            if ($this->search && $this->search != '') {
                $inbox->where(function ($query) {
                    $query->orWhere('messages.message', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->listing_type == 'draft') {
                if ($this->id) {
                    $inbox->where('message_statuses.message_id', $this->id);
                }
            } else {
                if ($this->parent_id) {
                    $inbox->where('messages.parent_id', $this->parent_id);
                }
            }

            $inbox->selectRaw($rawQuery);
            $inbox->orderByDesc('messages.created_at')
                ->limit($this->perPage);

            // $listingData = $inbox->get()->reverse()->values();
            $listingData = $inbox->get()->values();

            $this->hasMoreMessages = $inbox->count() >= $this->perPage;
            return $listingData;
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }
}