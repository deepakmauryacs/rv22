<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MiniWebPage;

class MiniWebPageController extends Controller
{
    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        $data = MiniWebPage::where('vendor_id', getParentUserId())->first();
        return view('vendor.web_pages.index', compact('data', 'user'));
    }
    public function store(Request $request)
    {
        //print_r($request->all());die;
        $data = MiniWebPage::where('vendor_id', $request->store_id)->first();
        if (empty($data)) {
            $data = new MiniWebPage();
        }
        $data->vendor_id = $request->store_id;
        $data->about_us = $request->about_us;

        $path = public_path('uploads/web-page/');

        // dd($request->all(), $request->hasFile('catalogue'));
        /***:- catalogue  -:***/
        if ($request->hasFile('catalogue')) {
            $file = $request->file('catalogue');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            // dd($filename);
            $file->move($path, $filename);
            if (isset($data->catalogue)) {
                $fullPath =  $path . $data->catalogue;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $data->catalogue = $filename;
        }

        /***:- certification  -:***/
        if ($request->hasFile('certification')) {
            $file = $request->file('certification');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move($path, $filename);
            if (isset($data->other_credentials)) {
                $fullPath =  $path . $data->other_credentials;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $data->other_credentials = $filename;
        }

        /***:- banner1  -:***/
        if ($request->hasFile('banner1')) {
            $file = $request->file('banner1');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move($path, $filename);
            if (isset($data->left_banner)) {
                $fullPath =  $path . $data->left_banner;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $data->left_banner = $filename;
        }

        /***:- right_banner  -:***/
        if ($request->hasFile('banner2')) {
            $file = $request->file('banner2');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move($path, $filename);
            if (isset($data->right_banner)) {
                $fullPath =  $path . $data->right_banner;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $data->right_banner = $filename;
        }
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => 'Data has been saved successfully'
        ]);
    }
}
