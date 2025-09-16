<?php
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserPlan;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OrdersPi;
use App\Models\Vendor;

if(!function_exists('orderPi'))
{
    function orderPi($orderId,$vendorId)
    {
        return OrdersPi::where('order_number', $orderId)->where('vendor_id', $vendorId)->first();
    }
}

if (!function_exists('xssCleanInput')) {
    // uses exmpale
    // Clean entire request input
    // $clean = xss_clean_input($request->all());
    // $request->merge($clean);
    // // Clean a single field
    // $name = xss_clean_input($request->input('name'));
    // // Clean raw array
    // $cleanArray = xss_clean_input(['name' => '<b>Hi</b>', 'msg' => '<script>bad()</script>']);
    function xssCleanInput(array|string $input): array|string
    {
        if (is_array($input)) {
            array_walk_recursive($input, function (&$value) {
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            });
            return $input;
        }
        // If input is a string or scalar
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('is_international_vendor_check')) {
    function is_international_vendor_check($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return 'User Not Found';
        }

        if ($user->country_code == '101') {
            return 'No';
        } else {
            return 'Yes';
        }
    }
}
if (!function_exists('getEmailTemplet')) {
    function getEmailTemplet(string $input): stdClass
    {
        return DB::table("email_templates")
                ->select("subject", "content as mail_message")
                ->where("name", $input)
                ->first();
    }
}
if (!function_exists('getBuyerEmailImages')) {
    function getBuyerEmailImages(): array
    {
        $my_logo = '<img alt="raProcure" src="'.asset('public/assets/images/rfq-logo.png').'" style="margin-left: 0px;max-width: 40%;margin-top: 0px;">';
        $base_url = url('/');

        $imgs = array();
        $imgs['my_logo'] = $my_logo;
        $imgs['raprocure_website_url'] = $base_url;
        $imgs['raprocure_website_domain'] = "www.raprocure.com";
        $imgs['raprocure_contact_number'] = "9088880077";
        $imgs['raprocure_support_mail'] = "support@raprocure.com";
        $imgs['telephone_fill_svg'] = '<img src="'.asset('public/assets/images/email-svg/telephone-fill.png').'" style="height: 20px; width: 20px; color:#800080">';
        $imgs['journal_text_svg'] = '<img src="'.asset('public/assets/images/email-svg/journal-text.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['clipboard2_check_svg'] = '<img src="'.asset('public/assets/images/email-svg/clipboard2-check.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['lightning_charge_svg'] = '<img src="'.asset('public/assets/images/email-svg/lightning-charge.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['footer_bg_svg'] = asset('public/assets/images/email-svg/image.png');
        return $imgs;
    }
}
if (!function_exists('buyerEmailTemplet')) {
    function buyerEmailTemplet(string $templet_name): stdClass
    {
        $mail_data = getEmailTemplet($templet_name);

        $mail_images = getBuyerEmailImages();

        $mail_msg = $mail_data->mail_message;

        $mail_msg = str_replace('$web_logo', $mail_images['my_logo'], $mail_msg);
        $mail_msg = str_replace('$raprocure_website_url', $mail_images['raprocure_website_url'], $mail_msg);
        $mail_msg = str_replace('$raprocure_website_domain', $mail_images['raprocure_website_domain'], $mail_msg);
        $mail_msg = str_replace('$raprocure_contact_number', $mail_images['raprocure_contact_number'], $mail_msg);
        $mail_msg = str_replace('$telephone_fill_svg', $mail_images['telephone_fill_svg'], $mail_msg);
        $mail_msg = str_replace('$journal_text_svg', $mail_images['journal_text_svg'], $mail_msg);
        $mail_msg = str_replace('$clipboard2_check_svg', $mail_images['clipboard2_check_svg'], $mail_msg);
        $mail_msg = str_replace('$lightning_charge_svg', $mail_images['lightning_charge_svg'], $mail_msg);
        $mail_msg = str_replace('$footer_bg_svg', $mail_images['footer_bg_svg'], $mail_msg);

        $mail_data->mail_message = $mail_msg;

        return $mail_data;
    }
}
if (!function_exists('getVendorEmailImages')) {
    function getVendorEmailImages(): array
    {
        $my_logo = '<img alt="raProcure" src="'.asset('public/assets/images/rfq-logo.png').'" style="margin-left: 0px;max-width: 40%;margin-top: 0px;">';
        $base_url = url('/');

        $imgs = array();
        $imgs['my_logo'] = $my_logo;
        $imgs['raprocure_website_url'] = $base_url;
        $imgs['raprocure_website_domain'] = "www.raprocure.com";
        $imgs['raprocure_contact_number'] = "9088880077";
        $imgs['raprocure_support_mail'] = "support@raprocure.com";
        $imgs['telephone_fill_svg'] = '<img src="'.asset('public/assets/images/email-svg/telephone-fill.png').'" style="height: 20px; width: 20px; color:#800080">';
        $imgs['globe2_svg'] = '<img src="'.asset('public/assets/images/email-svg/globe2.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['megaphone_svg'] = '<img src="'.asset('public/assets/images/email-svg/megaphone.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['lightning_charge_svg'] = '<img src="'.asset('public/assets/images/email-svg/lightning-charge.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['footer_bg_svg'] = asset('public/assets/images/email-svg/image.png');
        $imgs['journal_text_svg'] = '<img src="'.asset('public/assets/images/email-svg/journal-text.png').'" style="height: 20px; width: 15px; color:#800080">';
        $imgs['clipboard2_check_svg'] = '<img src="'.asset('public/assets/images/email-svg/clipboard2-check.png').'" style="height: 20px; width: 15px; color:#800080">';
        return $imgs;
    }
}
if (!function_exists('vendorEmailTemplet')) {
    function vendorEmailTemplet(string $templet_name): stdClass
    {
        $mail_data = getEmailTemplet($templet_name);
        $mail_msg = $mail_data->mail_message;

        $mail_images = getVendorEmailImages();

        $mail_msg = str_replace('$web_logo', $mail_images['my_logo'], $mail_msg);
        $mail_msg = str_replace('$raprocure_website_url', $mail_images['raprocure_website_url'], $mail_msg);
        $mail_msg = str_replace('$raprocure_website_domain', $mail_images['raprocure_website_domain'], $mail_msg);
        $mail_msg = str_replace('$raprocure_contact_number', $mail_images['raprocure_contact_number'], $mail_msg);
        $mail_msg = str_replace('$telephone_fill_svg', $mail_images['telephone_fill_svg'], $mail_msg);
        $mail_msg = str_replace('$globe2_svg', $mail_images['globe2_svg'], $mail_msg);
        $mail_msg = str_replace('$megaphone_svg', $mail_images['megaphone_svg'], $mail_msg);
        $mail_msg = str_replace('$lightning_charge_svg', $mail_images['lightning_charge_svg'], $mail_msg);
        $mail_msg = str_replace('$footer_bg_svg', $mail_images['footer_bg_svg'], $mail_msg);
        $mail_msg = str_replace('$support_mail', $mail_images['raprocure_support_mail'], $mail_msg);
        $mail_msg = str_replace('$journal_text_svg', $mail_images['journal_text_svg'], $mail_msg);
        $mail_msg = str_replace('$clipboard2_check_svg', $mail_images['clipboard2_check_svg'], $mail_msg);

        $mail_data->mail_message = $mail_msg;

        return $mail_data;
    }
}
if (!function_exists('getParentUserId')) {
    function getParentUserId(int $user_id=0): int
    {
        if($user_id!=0){
            $user= User::find($user_id);
            return empty($user->parent_id) ? $user->id : $user->parent_id;
        }else{
            return empty(Auth::user()->parent_id) ? Auth::user()->id : Auth::user()->parent_id;
        }
    }
}
if (!function_exists('getParentEmailId')) {
    function getParentEmailId(): string
    {
        if(empty(Auth::user()->parent_id)){
            return Auth::user()->email;
        }else{
            $parent_user = DB::table("users")->select("id", "email")->where("id", Auth::user()->parent_id)->first();
            return $parent_user->email;
        }
    }
}
if (!function_exists('getParentDetails')) {
    function getParentDetails(): object
    {
        return DB::table("users")->select("id", "name", "email", "country_code", "mobile")->where("id", getParentUserId())->first();
    }
}
if(!function_exists('getStateByCountryId'))
{
    function getStateByCountryId(int $country, int $state=0): string
    {
        $states = DB::table("states")
                            ->select("id", "name")
                            ->where("country_id", $country)
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();
        $html = '';
        if(!empty($states)){
            foreach($states as $id => $name){
                $html .= '<option value="'.$id.'" '.($state==$id ? "selected" : "").'>'.$name.'</option>';
            }
        }else{
            $html .='<option value="">No State Found...</option>';
        }
        return $html;
    }
}
if(!function_exists('getCityByStateId'))
{
    function getCityByStateId(int $state_id, int $city=0): string
    {
        $cities = DB::table("cities")
                            ->select("id", "city_name")
                            ->where("state_id", $state_id)
                            ->orderBy("city_name", "ASC")
                            ->pluck("city_name", "id")->toArray();
        $html = '';
        if(!empty($cities)){
            foreach ($cities as $id => $city_name) {
                $html.= '<option value="' . $id . '" '.($city==$id ? "selected" : "").'>' . $city_name . '</option>';
            }
        }else{
            $html .='<option value="">No City Found...</option>';
        }
        return $html;
    }
}

if(!function_exists('isDirExists'))
{
    function isDirExists(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
if(!function_exists('uploadFile'))
{
    function uploadFile(object $request, string $input, string $dir, string $file_prefix=''): array
    {
        try {
            $file = $request->file($input);
            $originalFile = preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filename = preg_replace('/\s+/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $customName = '';
            if($file_prefix!=''){
                $customName.= $file_prefix . '-';
            }
            $customName.= $filename . '-' . date('Ymd-His') . '.' . $extension;
            // Create the target directory for the current year
            $directory = public_path('uploads/'.$dir);


            isDirExists($directory);

            // Move the uploaded file to the desired directory
            // $imagePath = $file->move($directory, $file->getClientOriginalName());
            $imagePath = $file->move($directory, $customName);

            $fullPath = $directory . '/' . $customName;
            if (is_file($fullPath)) {
                $result = array('status' => true, 'file_name' => $customName);
            } else {
                $result = array('status' => false, 'file_name' => "File upload failed.");
            }
        } catch (\Exception $e) {
            // Log or return error
            $result = array('status' => false, 'file_name' => 'Uploading error: ' . $e->getMessage());
        }
        return $result;
    }
}
if(!function_exists('uploadMultipleFile'))
{
    function uploadMultipleFile(object $request, string $input, string $dir, int $key, string $file_prefix=''): array
    {
        try {
            $file = $request->file($input)[$key];
            $originalFile = preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filename = preg_replace('/\s+/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $customName = '';
            if($file_prefix!=''){
                $customName.= $file_prefix . '-';
            }
            $customName.=  $filename . '-' . date('Ymd-His') . '.' . $extension;
            // Create the target directory for the current year
            $directory = public_path('uploads/'.$dir);


            isDirExists($directory);

            // Move the uploaded file to the desired directory
            // $imagePath = $file->move($directory, $file->getClientOriginalName());
            $imagePath = $file->move($directory, $customName);

            $fullPath = $directory . '/' . $customName;
            if (is_file($fullPath)) {
                $result = array('status' => true, 'file_name' => $customName);
            } else {
                $result = array('status' => false, 'file_name' => "File upload failed.");
            }
        } catch (\Exception $e) {
            // Log or return error
            $result = array('status' => false, 'file_name' => 'Uploading error: ' . $e->getMessage());
        }
        return $result;
    }
}
if (!function_exists('removeFile')) {
    function removeFile(string $filePath): void {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

if (!function_exists('remove_extra_spaces')) {
    function remove_extra_spaces(string $str): string {
        // Remove extra spaces between words
        return preg_replace('/\s+/', ' ', trim($str));
    }
}

if(!function_exists('dateAfterDays')){
    function dateAfterDays(int $days_after): string
    {
        return now()->addDays($days_after)->format('d/m/Y');
    }
}

if(!function_exists('getMainSuperadminDetails')){
    function getMainSuperadminDetails(): object
    {
        return DB::table("users")
                ->select("id", "name", "email", "country_code", "mobile")
                ->where("parent_id", NULL)
                ->where("user_type", 3)
                ->first();
    }
}
if(!function_exists('htmlEntityDecodeWithLimit')){
    function htmlEntityDecodeWithLimit(string $str, int $limit): string
    {
        return Str::limit(html_entity_decode($str), $limit);
    }
}

if(!function_exists('sendNotifications')){
    function sendNotifications(array $notification_data): bool
    {
        $to_user_id = $notification_data['to_user_id'];
        $message_type = $notification_data['message_type'];
        $notification_link = parse_url($notification_data['notification_link'], PHP_URL_PATH);
        $notification_query = parse_url($notification_data['notification_link'], PHP_URL_QUERY);
        if(!empty($notification_query)){
            $notification_link .= '?'.$notification_query;
        }

        $sender_name = session('legal_name');
        if(Auth::user()->user_type==3){
            $sender_name = 'Raprocure';
        }

        $notification_message = match ($message_type) {
            'Buyer Account Creation' => "<b>".$sender_name."</b> has created a profile. Click here to verify",
            'Buyer Profile Update' => "Buyer <b>".$sender_name."</b> has updated profile. Click here to verify.",
            'RFQ Received' => (function () use ($notification_data, $sender_name) {
                return "New RFQ has been received from <b>".$sender_name."</b>. RFQ No. ".$notification_data['rfq_no'].".";
            })(),
            'Counter Offer Received' => (function () use ($notification_data) {
                return "Counter Offer has been received for RFQ No. ".$notification_data['rfq_no'].".";
            })(),
            'Order Cancelled' => (function () use ($notification_data) {
                return "Order - No ".$notification_data['po_number']." has been cancelled ";
            })(),
            'Order Confirmed' => (function () use ($notification_data) {
                return "Congratulations! You have received an Order. Order No.: ".$notification_data['po_number'].".";
            })(),
            'RFQ Closed' => (function () use ($notification_data, $sender_name) {
                return "<b>".$sender_name."</b> has closed RFQ No. ".$notification_data['rfq_id'].". You will no longer be able to quote.";
            })(),
            'RFQ Edited' => (function () use ($notification_data, $sender_name) {
                return "RFQ No. ".$notification_data['rfq_no']." has been edited. Update your quote accordingly.";
            })(),
            'RFQ Auction' => (function () use ($notification_data, $sender_name) {
                return "A new Auction has been scheduled. Time <b style='color:red;'>".$notification_data['auction_time']."</b> on <b style='color:red;'>".$notification_data['auction_date']."</b> for <b>".$notification_data['rfq_no']."</b> from <b>".$sender_name."</b> against RFQ No. ".$notification_data['rfq_no'].".";
            })(),
            'Forward Auction Created' => (function () use ($notification_data, $sender_name) {
                return "A new Forward Auction has been scheduled. Time <b style='color:red;'>".$notification_data['auction_time']."</b> on <b style='color:red;'>".$notification_data['auction_date']."</b> from <b>".$sender_name."</b> (Auction ID: ".$notification_data['auction_id'].").";
            })(),
            'Forward Auction Updated' => (function () use ($notification_data, $sender_name) {
                return "Forward Auction details have been updated. New schedule: Time <b style='color:red;'>".$notification_data['auction_time']."</b> on <b style='color:red;'>".$notification_data['auction_date']."</b> from <b>".$sender_name."</b> (Auction ID: ".$notification_data['auction_id'].").";
            })(),
            'Quotation to Buyer' => (function () use ($notification_data, $sender_name) {
                return "<b>".$sender_name."</b> has responded to your RFQ No. ".$notification_data['rfq_no'].". You can check their quote here.";
            })(),
            'Counter Offer to Buyer' => (function () use ($notification_data, $sender_name) {
                return "<b>".$sender_name."</b> has responded to the counter offer for RFQ No. ".$notification_data['rfq_no'].". You can check their revised quote here.";
            })(),
            'Buyer User Creation' => (function () use ($sender_name) {
                return "<b>".$sender_name."</b> has created a new user.";
            })(),
            // 'editor' => (function () {
            //     logAccess('editor');
            //     return 'Edit access granted';
            // })(),
            default => '',
        };

        if(!empty($notification_message)){
            try {
                if (is_numeric($to_user_id)) {
                    $notification['message']        =   $notification_message;
                    $notification['user_id']        =   $to_user_id;
                    $notification['sender_id']      =   Auth::user()->id;
                    $notification['link']           =   $notification_link;
                    $notification['sender_name']    =   $sender_name;
                    $notification['status']         =   2;
                    DB::table('notifications')->insert($notification);
                } else if (is_array($to_user_id)) {
                    $batch_notification = array();
                    foreach ($to_user_id as $key => $user_id) {
                        $notification = array();
                        $notification['message']        =   $notification_message;
                        $notification['user_id']        =   $user_id;
                        $notification['sender_id']      =   Auth::user()->id;
                        $notification['link']           =   $notification_link;
                        $notification['sender_name']    =   $sender_name;
                        $notification['status']         =   2;
                        $batch_notification[] = $notification;
                    }
                    if(!empty($batch_notification)){
                        DB::table('notifications')->insert($batch_notification);
                    }
                }
                return true;
            } catch (\Exception $e) {
                // Optional: log the error for debugging
                Log::error('Notifications Insert failed: ' . $e->getMessage());
                return false;
            }
        }
        return false;
    }
}
if(!function_exists('sendMultipleDBEmails')){
    function sendMultipleDBEmails(array $mail_arr): void {
        $buyer_id = Auth::user()->id;
        $all_mail_data = array();
        foreach ($mail_arr as $key => $value) {
            $all_mail_data[] = array(
                'user_id' => $buyer_id,
                'email' => $value['to'],
                'subject' => $value['subject'],
                'mail_data' => $value['body'],
                'created_at' => date("Y-m-d H:i:s")
            );
        }
        if (!empty($all_mail_data)) {
            DB::table('mail_data')->insert($all_mail_data);
        }
    }
}

if(!function_exists('getSADetails')){
    function getSADetails(): object {
        return DB::table('users')->select('id', 'name', 'email', 'mobile')->where('user_type', 3)->whereNull('parent_id')->first();
    }
}

if (!function_exists('setSessionWithExpiry')) {
    /**
     * Store a value in the session with an expiry timestamp.
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return void
     */
    function setSessionWithExpiry($key, $value, $minutes = 15)
    {
        session()->put($key, [
            'value' => $value,
            'expires_at' => now()->addMinutes($minutes)->timestamp,
        ]);
    }
}

if (!function_exists('getSessionWithExpiry')) {
    /**
     * Retrieve a session value with expiry and remove it if expired.
     *
     * @param string $key
     * @return mixed|null
     */
    function getSessionWithExpiry($key)
    {
        $data = session($key);
        if ($data && isset($data['expires_at'], $data['value'])) {
            if ($data['expires_at'] >= now()->timestamp) {
                // Not expired
                return $data['value'];
            } else {
                // Expired
                session()->forget($key);
                return null;
            }
        }
        return null;
    }
}


if(!function_exists('getBuyerUserBranch')){
    function getBuyerUserBranch(): array {
        if(empty(Auth::user()->parent_id)){
            $branch_data['user_type'] = "Buyer";
        }else{
            $user_branch_ids = DB::table('users_branch')
                ->select('branch_id')
                ->where("user_id", Auth::user()->id)
                ->pluck('branch_id')
                ->toArray();

            $branch_data['user_type'] = "Buyer-User";
            $branch_data['user_branch_id_arr'] = $user_branch_ids;
        }
        return $branch_data;
    }
}
if(!function_exists('getBuyerUserBranchIdOnly'))
{
    function getBuyerUserBranchIdOnly(): array {
        $buyer_user_branch = getBuyerUserBranch();
        $branch_id_only = array();
        if($buyer_user_branch['user_type'] == "Buyer-User"){
            $branch_id_only = $buyer_user_branch['user_branch_id_arr'];
        }
        return $branch_id_only;
    }
}

if(!function_exists('getBuyerBranchs'))
{
    function getBuyerBranchs(array $branch_ids=array()): array {
        $company_id = getParentUserId();
        $branch = DB::table('branch_details')
            ->select('id', 'branch_id', 'name')
            ->where("user_id", $company_id)
            ->where('user_type', 1)
            ->where('record_type', 1)
            ->where('status', 1)
            ->orderBy('branch_id', "ASC");
        if(!empty($branch_ids)){
            $branch->where_in('branch_id', $branch_ids);
        }
        return $branch->get()->toArray();
    }
}
if(!function_exists('generateRFQDraftNumber'))
{
    function generateRFQDraftNumber(string $id): string {
        // Convert numeric ID to string
        $base10Str = (string) $id;

        // Take the last 9 digits only (to keep length fixed)
        $base10Str = substr($base10Str, -9);

        // Calculate the length of padding needed
        $pad_len = 9 - strlen($base10Str);

        // Generate random digits (1-9) for padding
        $random_pad = '';
        for ($i = 0; $i < $pad_len; $i++) {
            $random_pad .= rand(1, 9);
        }

        // Combine random padding with base10Str
        $padded = $random_pad . $base10Str;

        // Prefix with 'D' to make total length 10 characters
        return 'D' . $padded;
    }
}
if(!function_exists('generateBuyerRFQNumber'))
{
    function generateBuyerRFQNumber(int $company_id): string {
        $buyer = DB::table('buyers')
            ->select('id', 'organisation_short_code', 'rfq_number')
            ->where("user_id", $company_id)
            ->first();

        $next_rfq_number = ((int) $buyer->rfq_number)+1;
        $year = substr(date("Y"), -2);

        DB::table('buyers')->where("user_id", $company_id)->update(['rfq_number'=>$next_rfq_number]);

        return $buyer->organisation_short_code . '-' . $year . '-' . str_pad($next_rfq_number, 5, "0", STR_PAD_LEFT);
    }
}
if(!function_exists('validateRFQNumber'))
{
    function validateRFQNumber(string $rfq_number): bool {
        $is_exists = DB::table('rfqs')
            ->select('id')
            ->where("rfq_id", $rfq_number)
            ->first();

        return !empty($is_exists) ? false : true;
    }
}
if(!function_exists('generateBuyerCode'))
{
    function generateBuyerCode(int $state_id): string {
        $setting = DB::table('setting')
            ->select('id', 'buyer_code_id')
            ->first();
        $state_code = '';
        if(!empty($state_id)){
            $state = DB::table('states')
                ->where('id', $state_id)
                ->first();
            if(!empty($state) && !empty($state->state_code)){
                $state_code = $state->state_code;
            }
        }

        $next_buyer_number = ((int) $setting->buyer_code_id)+1;
        DB::table('setting')->where("id", $setting->id)->update(['buyer_code_id'=>$next_buyer_number]);
        return 'B-' .(!empty($state_code) ? $state_code . '-' : '' ). str_pad($next_buyer_number, 3, "0", STR_PAD_LEFT);
    }
}
if(!function_exists('generateVendorCode'))
{
    function generateVendorCode(int $state_id): string {
        $setting = DB::table('setting')
            ->select('id', 'vendor_code_id')
            ->first();

        $state_code = '';
        if(!empty($state_id)){
            $state = DB::table('states')
                ->where('id', $state_id)
                ->first();
            if(!empty($state) && !empty($state->state_code)){
                $state_code = $state->state_code;
            }
        }

        $next_vendor_number = ((int) $setting->vendor_code_id)+1;
        DB::table('setting')->where("id", $setting->id)->update(['vendor_code_id'=>$next_vendor_number]);
        return 'V-' .(!empty($state_code) ? $state_code . '-' : '' ). str_pad($next_vendor_number, 3, "0", STR_PAD_LEFT);
    }
}
if(!function_exists('generateCombinations')){
    function generateCombinations($arr, $n='') {
        $result = array();
        if($n==''){
            $n = count($arr);
        }
        for ($i = 1;$i <= $n;$i++) {
            combine($arr, $n, $i, 0, [], $result);
        }
        return $result;
    }
}
if(!function_exists('combine')){
    function combine($arr, $n, $len, $start, $temp, &$result) {
        if (count($temp) == $len) {
            $result[] = $temp;
            return;
        }
        for ($i = $start;$i < $n;$i++) {
            $temp[] = $arr[$i]??'';
            combine($arr, $n, $len, $i + 1, $temp, $result);
            array_pop($temp);
        }
    }
}

if(!function_exists('getCityStateCountry')){
    function getCityStateCountry(int $id,string $type): string
    {
        if($type=='city')
        {
            return DB::table("cities")->select("city_name")->where("id", $id)->first()->city_name;
        }
        if($type=='state')
        {
            return DB::table("states")->select("name")->where("id", $id)->first()->name;
        }
        if($type=='country')
        {
            return DB::table("countries")->select("name")->where("id", $id)->first()->name;
        }
        return '';
    }
}
if (!function_exists('amounts_number_to_words_with_currency')) {
    function amounts_number_to_words_with_currency($number, $currency = '₹') {
        $number = floatval($number);
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $i = 0;
        $str = [];
        $words = [
            '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four',
            '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
            '10' => 'ten', '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen',
            '14' => 'fourteen', '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
            '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty', '30' => 'thirty',
            '40' => 'forty', '50' => 'fifty', '60' => 'sixty', '70' => 'seventy',
            '80' => 'eighty', '90' => 'ninety'
        ];
        $digits = ['', 'hundred', 'thousand', 'lakh', 'crore', 'arab', 'kharab', 'neel', 'padma', 'shankh'];

        while ($no > 0) {
            $divider = ($i == 2) ? 10 : 100;
            $number = $no % $divider;
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;

            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' ' : null;
                $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                        : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }

        $str = array_reverse($str);
        $result = implode('', $str);

        $points = ($point > 20) ? $words[floor($point / 10) * 10] . " " . $words[$point % 10] : $words[$point];
        $is_points = !empty($points) ? 'And ' . ucwords($points) . ' Cents ' : "";

        // Define currency suffix based on the currency parameter
        switch ($currency) {
            case '$':
                $currency_suffix = 'Dollars';
                $point_suffix = 'Cents';
                break;
            case 'NPR':
                $currency_suffix = 'Nepali Rupees';
                $point_suffix = 'Paisa';
                break;
            case '₹':
            default:
                $currency_suffix = 'Rupees';
                $point_suffix = 'Paise';
                break;
        }

        return ucwords($currency_suffix) . " " . ucwords($result) . ($is_points ? 'And ' . ucwords($points) . " $point_suffix " : '') . 'Only';
    }
}
if (!function_exists('amounts_number_to_words')) {
    function amounts_number_to_words($number)
    {
        $number = floatval($number);
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            '0' => '', '1' => 'one', '2' => 'two', '3' => 'three',
            '4' => 'four', '5' => 'five', '6' => 'six', '7' => 'seven',
            '8' => 'eight', '9' => 'nine', '10' => 'ten', '11' => 'eleven',
            '12' => 'twelve', '13' => 'thirteen', '14' => 'fourteen',
            '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
            '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty',
            '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
            '60' => 'sixty', '70' => 'seventy', '80' => 'eighty',
            '90' => 'ninety'
        ];
        $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];

        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;

            if ($number) {
                $plural = ((count($str)) && $number > 9) ? '' : null;
                $hundred = ((count($str) == 1) && $str[0]) ? ' ' : null;
                $str[] = ($number < 21)
                    ? $words[$number] . " " . $digits[count($str)] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[count($str)] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }

        $str = array_reverse($str);
        $result = implode('', $str);

        $points = ($point > 20)
            ? $words[floor($point / 10) * 10] . " " . $words[$point % 10]
            : $words[$point];

        $is_points = !empty($points) ? 'And ' . ucwords($points) . ' Paise ' : "";

        return 'Rupees ' . ucwords(trim($result)) . " " . $is_points . 'Only';
    }
}

if (!function_exists('IND_money_format')) {
    function IND_money_format($number)
    {
        $number = (string) $number;
        $number_array = explode('.', $number);
        $integer_part = $number_array[0];
        $decimal_part = isset($number_array[1]) ? $number_array[1] : null;

        $last_three = substr($integer_part, -3);
        $rest_units = substr($integer_part, 0, -3);

        if ($rest_units != '') {
            $rest_units = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest_units);
            $formatted = $rest_units . ',' . $last_three;
        } else {
            $formatted = $last_three;
        }

        if ($decimal_part && (int)$decimal_part !== 0) {
            $formatted .= '.' . substr($decimal_part, 0, 2); // Keep only 2 decimal digits
        }

        return $formatted;
    }
}



if (!function_exists('getNoOfUsersByUserId')) {
    /**
     * Get the number of users allowed for a given user_id's plan.
     *
     * @param int $userId
     * @return int|null
     */
    function getNoOfUsersByUserId($userId)
    {
        // Get the latest active user plan for this user
        $userPlan = UserPlan::where('user_id', $userId)
            // ->where('is_expired', '2') // Not expired
            ->orderByDesc('id')
            ->first();

        if ($userPlan) {
            // If no_of_users is set, return it
            if (!is_null($userPlan->no_of_users)) {
                return $userPlan->no_of_users;
            }

            // Otherwise, get plan_id and fetch from plans table
            if ($userPlan->plan_id) {
                $plan = Plan::find($userPlan->plan_id);
                if ($plan && !is_null($plan->no_of_user)) {
                    return $plan->no_of_user;
                }
            }
        }

        // Return null if nothing found
        return null;
    }
}

if (!function_exists('getTotalUserAccountsByUserId')) {
    /**
     * Get total user accounts (parent + child accounts)
     *
     * @param int $userId
     * @return int
     */
    function getTotalUserAccountsByUserId($userId)
    {
        // Count child accounts with parent_id = $userId
        $childCount = User::where('parent_id', $userId)->count();
        // Add 1 for the main account
        return $childCount + 1;
    }
}

if (!function_exists('getMessageCount')) {
    /**
    * get total unread message count for buyer, vendor and raprocure
    */
    function getMessageCount()
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
}

if (!function_exists('is_national')) {
    /**
     * Check if current user (or given user) is a national vendor
     *
     * @param int|null $vendorId
     * @return int 1 = National, 0 = International/unknown
     */
    function is_national($vendorId = null)
    {
        if (empty($vendorId)) {
            if (!Auth::check()) {
                return 0; // No user logged in
            }

            $vendorId = empty(Auth::user()->parent_id) ? Auth::user()->id : Auth::user()->parent_id;
        }

        $vendor = \App\Models\Vendor::where('user_id', $vendorId)->first();

        if (!$vendor) {
            return 0; // No vendor record found
        }

        return ((int) $vendor->country === 101) ? 1 : 0;
    }
}


if (!function_exists('is_national_buyer')) {
    /**
     * Check if current user (or given user) is a national buyer
     *
     * @param int|null $buyerId
     * @return int 1 = National, 0 = International/Unknown
     */
    function is_national_buyer($buyerId = null)
    {
        if (empty($buyerId)) {
            if (!Auth::check()) {
                return 0; // No user logged in
            }

            $buyerId = empty(Auth::user()->parent_id) ? Auth::user()->id : Auth::user()->parent_id;
        }

        $buyer = \App\Models\Buyer::where('user_id', $buyerId)->first();

        if (!$buyer) {
            return 0; // No buyer record found
        }

        return ((int) $buyer->country === 101) ? 1 : 0;
    }
}

if (!function_exists('getbuyerBranchById')) {
    function getbuyerBranchById($branchId)
    {
        return DB::table('branch_details')
            ->where('branch_id', $branchId)
            ->where('user_type',1)
            ->where('record_type', '1')
            ->first();
    }
}
if (!function_exists('getbuyerAllBranch')) {
    function getbuyerAllBranch($user_id)
    {
        return DB::table('branch_details')
            ->select('branch_id', 'name')
            ->where('user_id', $user_id)
            ->where('user_type', 1)
            ->where('record_type', '1')
            ->pluck('name', 'branch_id')->toArray();
    }
}
if (!function_exists('getVendorBranchById')) {
    function getVendorBranchById($branchId)
    {
        return DB::table('branch_details')
            ->where('branch_id', $branchId)
            ->where('user_type',2)
            ->where('record_type', '1')
            ->first();
    }
}
if (!function_exists('getUOMList')) {
    function getUOMList()
    {
        return DB::table('uoms')
            ->select('id', 'uom_name')
            ->where('status', '1') // only active
            ->orderBy("id", "ASC")
            ->pluck("uom_name", "id")->toArray();
    }
}
if (!function_exists('getUOMName')) {
    function getUOMName($id)
    {
        if (!$id) {
            return null;
        }

        return DB::table('uoms')
            ->where('id', $id)
            ->where('status', '1') // only active
            ->value('uom_name');
    }
}
if (!function_exists('getAuctionStatus')) {
    function getAuctionStatus($date, $start_time, $end_time)
    {
        $start_datetime = strtotime($date . ' ' . $start_time);
        $end_datetime = strtotime($date . ' ' . $end_time);
        $now = time();

        if ($now > $end_datetime) {
            $auction_status = 3; // Past- Completed
        } elseif ($now >= $start_datetime && $now <= $end_datetime) {
            $auction_status = 1; // Ongoing- Live
        } else {
            $auction_status = 2; // Upcoming- Scheduled
        }
        return $auction_status;
    }
}

if (!function_exists('common_rfq_data')) {
    function common_rfq_data($rfq_id)
    {
        $vendor_id = empty(Auth::user()->parent_id) ? Auth::user()->id : Auth::user()->parent_id;

        if (!$rfq_id || !$vendor_id) {
            return null;
        }

        return \App\Models\RfqVendorQuotation::select(
                'vendor_remarks',
                'vendor_additional_remarks',
                'vendor_price_basis',
                'vendor_payment_terms',
                'vendor_delivery_period',
                'vendor_price_validity',
                'vendor_dispatch_branch',
                'vendor_currency'
            )
            ->where('vendor_user_id', $vendor_id)
            ->whereHas('rfqProductVariant', function($query) use ($rfq_id) {
                $query->where('rfq_id', $rfq_id);
            })
            ->orderByDesc('id')
            ->first();
    }
}

if (!function_exists('common_rfq_auction_data')) {
    function common_rfq_auction_data($rfq_id)
    {
        $vendor_id = empty(Auth::user()->parent_id) ? Auth::user()->id : Auth::user()->parent_id;

        if (!$rfq_id || !$vendor_id) {
            return null;
        }

        return \App\Models\RfqVendorAuctionPrice::select(

                'vend_price_basis as vendor_price_basis',
                'vend_payment_terms as vendor_payment_terms',
                'vend_delivery_period as vendor_delivery_period',
                'vend_price_validity as vendor_price_validity',
                'vend_dispatch_branch as vendor_dispatch_branch',
                'vend_currency as vendor_currency'
            )
            ->where('vendor_user_id', $vendor_id)
            ->whereHas('rfqProductVariant', function($query) use ($rfq_id) {
                $query->where('rfq_id', $rfq_id);
            })
            ->orderByDesc('id')
            ->first();
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($currency)
    {
        $currency_symbols = [
            '₹'   => '₹',        // INR symbol
            '$'   => '$',        // USD symbol
            'NPR' => 'NPR',      // Nepali Rupee (use symbol as needed)
            'रु'  => 'रु',       // Nepali Rupee (Devanagari script)
        ];

        $vendor_currency_map = [
            1 => '₹',        // Vendor ID 1 uses INR
            2 => '$',        // Vendor ID 2 uses USD
            3 => 'रु',       // Vendor ID 3 uses NPR
        ];

        // If input is numeric, treat it as vendor ID and get symbol
        if (is_numeric($currency)) {
            return $vendor_currency_map[$currency] ?? '₹';
        }

        // Check if input is a currency symbol or code
        return $currency_symbols[$currency] ?? '₹';
    }
}
if (!function_exists('get_currency_str')) {
    function get_currency_str($currency)
    {
        // Define currency symbols with symbols as keys and currency codes as values
        $currency_symbols = [
            '₹' => 'INR', // Indian Rupee
            '$' => 'USD', // US Dollar
            'रु' => 'NPR', // Nepali Rupee
            'NPR' => 'NPR', // Nepali Rupee
            // You can add more currency symbols here
        ];
        // Check if input is a currency symbol
        return isset($currency_symbols[$currency]) ? $currency_symbols[$currency] : 'INR'; // Default to INR if not found
    }
}
if (!function_exists('log_peak_memory_usage')) {
    function log_peak_memory_usage($msg='')
    {
        $memory = memory_get_peak_usage(true);
        $memoryMB = round($memory / 1024 / 1024, 2);
        $currentUrl = \Illuminate\Support\Facades\Request::fullUrl();
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
        \Illuminate\Support\Facades\Log::info(($msg!='' ? $msg.': ' : '')."Peak memory usage: {$memoryMB} MB | URL: {$currentUrl} | Route: {$routeName}");
    }
}

if (!function_exists('setActiveMenu')) {
    function setActiveMenu(string|array $patterns, string $class_name = 'active-item'): string {

        if (is_array($patterns)) {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return $class_name;
                }
            }
            return '';
        } else {
            // Single string route name
            return request()->routeIs($patterns) ? $class_name : '';
        }
    }
}

if (!function_exists('setParentActiveMenu')) {
    function setParentActiveMenu(string|array $patterns): array {
        $isActive = false;
        if (is_array($patterns)) {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    $isActive = true;
                    break;
                }
            }
        } else {
            $isActive = request()->routeIs($patterns);
        }

        // Return classes for accordion button and collapse div based on active state
        return [
            'button_class' => $isActive ? 'accordion-button' : 'accordion-button collapsed',
            'collapse_class' => $isActive ? 'accordion-collapse collapse show' : 'accordion-collapse collapse',
            'aria_expanded' => $isActive ? 'true' : 'false'
        ];
    }

}
if (!function_exists('makeDuplicateRFQData')) {
    function makeDuplicateRFQData(array $rfqArray, $record_type, $type='re-use'): string {

        $buyer_id = getParentUserId();
        $current_user_id = Auth::user()->id;

        $edited_rfq_id = $type == 'edit' ? $rfqArray['rfq_id'] : NULL;

        // Insert RFQ as before (need the newRfqId for children)
        $newRfqArray = $rfqArray;
        unset($newRfqArray['rfq_products']);
        unset($newRfqArray['rfq_product_variants']);
        unset($newRfqArray['rfq_vendors']);
        unset($newRfqArray['id']);
        unset($newRfqArray['updated_at']);
        unset($newRfqArray['created_at']);

        $newRfqArray['rfq_id'] = '';
        $newRfqArray['record_type'] = $record_type;
        $newRfqArray['buyer_id'] = $buyer_id;
        $newRfqArray['is_bulk_rfq'] = 2;
        // $newRfqArray['buyer_rfq_status'] = 1;
        $newRfqArray['buyer_user_id'] = $current_user_id;
        $newRfqArray['last_response_date'] = \Carbon\Carbon::parse($newRfqArray['last_response_date'])->format('Y-m-d');
        $newRfqArray['edit_by'] = $type == 'edit' ? $current_user_id : NULL;
        $newRfqArray['edit_rfq_id'] = $edited_rfq_id;
        $rfqInsertId = DB::table('rfqs')->insertGetId($newRfqArray);

        $new_draft_id = generateRFQDraftNumber($rfqInsertId);
        DB::table('rfqs')->where('id', $rfqInsertId)->update(['rfq_id' => $new_draft_id]);
        unset($newRfqArray);

        // Prepare batch inserts for products
        if (!empty($rfqArray['rfq_products'])) {
            $productsToInsert = [];
            foreach ($rfqArray['rfq_products'] as $product) {
                unset($product['id']);
                unset($product['updated_at']);
                unset($product['created_at']);
                $product['rfq_id'] = $new_draft_id;
                $product['edit_rfq_id'] = $edited_rfq_id;
                $productsToInsert[] = $product;
            }
            DB::table('rfq_products')->insert($productsToInsert);
            unset($productsToInsert);
        }

        // Prepare batch inserts for product variants
        if (!empty($rfqArray['rfq_product_variants'])) {
            $variantsToInsert = [];
            foreach ($rfqArray['rfq_product_variants'] as $variant) {
                $variant['edit_id'] = $type == 'edit' ? $variant['id'] : NULL;
                unset($variant['id']);
                unset($variant['updated_at']);
                unset($variant['created_at']);
                $variant['rfq_id'] = $new_draft_id;
                $variant['variant_grp_id'] = ((int) microtime(true)) . mt_rand(10000, 99999);
                $variantsToInsert[] = $variant;
            }
            DB::table('rfq_product_variants')->insert($variantsToInsert);
            unset($variantsToInsert);
        }

        // Prepare batch inserts for vendors
        if (!empty($rfqArray['rfq_vendors'])) {
            $vendorsToInsert = [];
            foreach ($rfqArray['rfq_vendors'] as $vendor) {
                unset($vendor['id']);
                unset($vendor['updated_at']);
                unset($vendor['created_at']);
                $vendor['rfq_id'] = $new_draft_id;
                $vendor['vendor_status'] = 1;
                $vendorsToInsert[] = $vendor;
            }
            DB::table('rfq_vendors')->insert($vendorsToInsert);
            unset($vendorsToInsert);
        }
        unset($rfqArray);

        return $new_draft_id;
    }

}
if (!function_exists('encrypt_decrypt_urlsafe')) {
function encrypt_decrypt_urlsafe($action, $string) {
    $output = false;

    // Use your own secret key and IV
    $secret_key = 'your_secret_key_123';
    $secret_iv = 'your_secret_iv_123';

    $encrypt_method = "AES-256-CBC";
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action === 'encrypt') {
        $encrypted = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $base64 = base64_encode($encrypted);
        // Make it URL-safe
        $output = rtrim(strtr($base64, '+/', '-_'), '=');
    } else if ($action === 'decrypt') {
        // Reverse the URL-safe transformation
        $base64 = strtr($string, '-_', '+/');
        $base64 = str_pad($base64, strlen($base64) % 4 === 0 ? strlen($base64) : strlen($base64) + 4 - strlen($base64) % 4, '=', STR_PAD_RIGHT);
        $decoded = base64_decode($base64);
        $output = openssl_decrypt($decoded, $encrypt_method, $key, 0, $iv);
    }

    return $output;
}
}
