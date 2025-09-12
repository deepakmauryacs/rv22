<?php

namespace App\Http\Controllers\Vendor;

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
        $total = $total > 9 ? '9+' : $total;
        $notifications=$notifications->take(4);
        $html='<div class="message-wrap">';
        $status_color=[
            '0' => 'notification-bg-blue',
            '1' => 'notification-bg-pink',
            '2' => 'notification-bg-yellow',
            '3' => 'notification-bg-green'
        ];
        $baseHost = request()->getSchemeAndHttpHost();
        //$rootUrl = request()->root();
        foreach($notifications as $key => $notification) {
            $html.='<div class="message-wrapper '. $status_color[$key].'">
                        <div class="message-detail">
                            <a href="'.$baseHost.$notification->link.'" onclick="readNotification(`'.encrypt_decrypt_urlsafe('encrypt',$notification->id).'`)">
                                <div class="message-head-line">
                                    <div class="person-name">
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
        $html.=' <div class="text-center"><a href="'.route('vendor.notification.index').'" class="view-notification">View All Notification</a></div>
                </div>';
        $html1='<div class="message-wrap">
                <div class="message-wrapper notification-bg-blue">
                  <div class="message-detail">
                    <a href="javascript:void(0)">
                      <div class="message-head-line">
                        <div class="person-name">
                          <span>RON PVT LTD</span>
                        </div>
                        <p class="message-body-line">
                          02 Jun, 2025 12:53 PM
                        </p>
                      </div>
                      <p class="message-body-line">
                        New RFQ has been received from RON PVT LTD. RFQ No. RONI-25-00038.
                      </p>
                    </a>
                  </div>
                </div>
                <div class="message-wrapper notification-bg-pink">
                  <div class="message-detail">
                    <a>
                      <div class="message-head-line">
                        <div class="person-name">
                          <span>A KUMAR</span>
                        </div>
                        <p class="message-body-line">
                          26 Mar, 2025 05:12 PM
                        </p>
                      </div>
                      <p class="message-body-line">
                        A KUMAR has responded to your RFQ No.
                        RATB-25-00046. You can check their quote here
                      </p>
                    </a>
                  </div>
                </div>
                <div class="message-wrapper notification-bg-yellow">
                  <div class="message-detail">
                    <a>
                      <div class="message-head-line">
                        <div class="person-name">
                          <span>TEST AMIT VENDOR</span>
                        </div>
                        <p class="message-body-line">
                          26 Mar, 2025 04:35 PM
                        </p>
                      </div>
                      <p class="message-body-line">
                        TEST AMIT VENDOR has responded to your RFQ No.
                        RATB-25-00046. You can check their quote here
                      </p>
                    </a>
                  </div>
                </div>
                <div class="message-wrapper notification-bg-green">
                  <div class="message-detail">
                    <a>
                      <div class="message-head-line">
                        <div class="person-name">
                          <span>A KUMAR</span>
                        </div>
                        <p class="message-body-line">
                          26 Mar, 2025 04:35 PM
                        </p>
                      </div>
                      <p class="message-body-line">
                        A KUMAR has responded to your RFQ No.
                        RATB-25-00046. You can check their quote here
                      </p>
                    </a>
                  </div>
                </div>
                <div class="text-center"><a href="http://localhost/raProcureV2/vendor/notification" class="view-notification">View All
                    Notification</a></div>
              </div>';
        return response()->json(['count'=>$total,'html'=>$html], 200);
    }
}
