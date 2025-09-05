<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class BuyerDashboardController extends Controller
{

    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        // echo session()->getId();die;
        return view('buyer.buyer-dashboard', compact('user'));
    }
    public function change_password()
    {
        $user = Auth::user();
        return view('buyer.setting.change-password', compact('user'));
    }

    public function updatePassword(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'currentpassword' => 'required',
            'changepassword' => 'required|min:8',
            'confirmpassword' => 'required|same:changepassword',
         ],[
            'currentpassword.required' => 'Current Password Required',
            'changepassword.required' => 'Change Password Required',
            'changepassword.min' => 'Change Password must be 8 digit or greater then 8 digit !',
            'confirmpassword.required' => 'Confirm Password Required',
            'confirmpassword.same' => 'Confirm Password and Change Password must be same',
         ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $user = Auth::user();
        if(!Hash::check($request->currentpassword, $user->password)){
            return redirect()->back()->with('error', 'Current password is incorrect');
        }
        $user->password = Hash::make($request->confirmpassword);
        $user->save();
        return redirect()->back()->with('success', 'Password updated successfully');
    }
}
