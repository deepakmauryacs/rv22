<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\EmailHelper;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class AddYourVendorController extends Controller
{
    public function index()
    {
        return view('buyer.add-vendor.create');
    }

    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'vendor_company' => ['required', 'array'],
            'vendor_company.*' => ['required', 'string', 'max:255'],
            
            'contact_person' => ['required', 'array'],
            'contact_person.*' => ['required', 'string', 'max:255'],
            
            'email_id' => ['required', 'array'],
            'email_id.*' => ['required', 'string', 'email', 'max:255'],
            
            'phone_number' => ['required', 'array'],
            'phone_number.*' => ['required', 'string', 'max:255'],
            
            'product_name' => ['required', 'array'],
            'product_name.*' => ['required', 'string', 'max:255'],
            
            'product_category' => ['required', 'array'],
            'product_category.*' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $vendor_company=$request->vendor_company;
        $contact_person=$request->contact_person;
        $email_id=$request->email_id;
        $phone_number=$request->phone_number;
        $product_name=$request->product_name;
        $product_category=$request->product_category;
        $is_invalid_email = false;
        $email_error_row = array();
        foreach ($vendor_company as $key => $value) {
            if(!empty($email_id[$key])){
                $all_emails = explode(",", $email_id[$key]);
                foreach ($all_emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $is_invalid_email = true;
                        $email_error_row[] = $key;
                    }
                }   
            }
        }
        if($is_invalid_email){
            return response()->json([
                'status' => false,
                'message' => 'Invalid email address found in row '.implode(", ", $email_error_row)
            ]);
        }

        $buyer_id= getParentUserId();
        $buyer=User::with('buyer')->find($buyer_id);
        $buyer_email=$buyer->email;
        $buyer_company_name = $buyer->buyer->legal_name;
        $buyer_name = $buyer->name;
        $mail_data          =vendorEmailTemplet('add-your-vendor-to-vendor');
        $message          = $mail_data->mail_message;
        $subject        = $mail_data->subject;

        //$admin_msg          =vendorEmailTemplet('add-your-vendor-to-vendor');
        $subject        = str_replace('$buyer_name', $buyer_company_name, $subject);
        $message          = str_replace('$buyer_company_name', $buyer_company_name, $message);
        $message          = str_replace('$user_name', $buyer_name, $message);
       
        $mail_arr = '';
        foreach ($vendor_company as $key => $value) {
            $message = $message;
            $message = str_replace('$vendor_company_name', $vendor_company[$key], $message);
            $all_emails = explode(",", $email_id[$key]);
            foreach ($all_emails as $key_email => $val_email) {
                $val_email = trim($val_email);
                EmailHelper::sendMail($val_email,$subject, $message);
            }
            $mail_arr .= '<tr>
                                <td class="vendor-tbls">'.$vendor_company[$key].'</td>
                                <td class="vendor-tbls">'.$contact_person[$key].'</td>
                                <td class="vendor-tbls">'.$email_id[$key].'</td>
                                <td class="vendor-tbls">'.$phone_number[$key].'</td>
                                <td class="vendor-tbls">'.$product_name[$key].'</td>
                                <td class="vendor-tbls">'.$product_category[$key].'</td>
                            </tr>';

        }
        $sa_email         = '';
         
        $sa_mail_data          = vendorEmailTemplet('add-your-vendor-to-super-admin');
        $sa_admin_msg          = $sa_mail_data->mail_message;
        $sa_subject            = $sa_mail_data->subject; 
        //$sa_admin_msg          = vendorEmailTemplet($sa_admin_msg);
        
        $sa_subject            = str_replace('$buyer_name', $buyer_company_name, $sa_subject);
        $sa_admin_msg          = str_replace('$buyer_company_name', $buyer_company_name, $sa_admin_msg);
        $sa_admin_msg          = str_replace('$vendor_details', $mail_arr, $sa_admin_msg);

        EmailHelper::sendMail($sa_email,$sa_subject, $sa_admin_msg);
        $res['message'] = "Your Vendor request has been submitted successfully!";
        $res['status'] = true;
        return response()->json($res);
    }
}
