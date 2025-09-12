<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Notification;
class CommonController extends Controller
{
    public function getStateByCountryId(Request $request)
    {
        $country_id = $request->country_id;
        if(empty($country_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }

        $states = DB::table("states")
                            ->select("id", "name")
                            ->where("country_id", $country_id)
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();
        $html = '';
        foreach ($states as $id => $name) {
            $html.= '<option value="' . $id . '">' . $name . '</option>';
        }
        return response()->json([
            'status' => true,
            'message' => 'State List',
            'state_list' => $html
        ]);
    }
    public function getCityByStateId(Request $request)
    {
        $state_id = $request->state_id;
        if(empty($state_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }

        $cities = DB::table("cities")
                            ->select("id", "city_name")
                            ->where("state_id", $state_id)
                            ->orderBy("city_name", "ASC")
                            ->pluck("city_name", "id")->toArray();
        $html = '';
        foreach ($cities as $id => $city_name) {
            $html.= '<option value="' . $id . '">' . $city_name . '</option>';
        }
        return response()->json([
            'status' => true,
            'message' => 'City List',
            'city_list' => $html
        ]);
    }

    public function notification(Request $request) {
        $notifications = Notification::where('user_id', getParentUserId())->where('status','2')->orderBy('id', 'desc')->get();
        $total=$notifications->count();
        $total=$total>9 ? '9+' : $total;
        $notifications=$notifications->take(4);
        $html='<div class="message_wrap">';
        $status_color=[
            '0' => 'notification-bg-blue',
            '1' => 'notification-bg-pink',
            '2' => 'notification-bg-yellow',
            '3' => 'notification-bg-green'
        ];
        $baseHost = request()->getSchemeAndHttpHost();
        foreach($notifications as $key => $notification) {
            $html.='<div class="message-wrapper '. $status_color[$key].'">
                        <div class="message-detail">
                            <a href="'.$baseHost.$notification->link.'" onclick="readNotification(`'.encrypt_decrypt_urlsafe('encrypt',$notification->id).'`)">
                                <div class="message-head-line">
                                    <div class="person_name">
                                        <span>'.$notification->sender_name.'</span>
                                    </div>
                                    <p class="message-body-line">
                                        '.date('M, d, Y h:i A', strtotime($notification->created_at)).'
                                    </p>
                                </div>
                                <p class="message-body-line">
                                    '.$notification->message.'
                                </p>
                            </a>
                        </div>
                    </div>';
        }
        $html.='<a href="'.route('buyer.notification.index').'">View All Notification</a>
                </div>';
        return response()->json(['count'=>$total,'html'=>$html], 200);
    }
}
