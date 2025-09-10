<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Category;
// use App\Models\Division;
// use App\Models\LiveVendorProduct;
use App\Models\RfqVendor;
use App\Models\Rfq;
use Carbon\Carbon;
use DB;
use Auth;
use Validator;
use App\Traits\HasModulePermission;

class TechnicalApprovalController extends Controller
{
    use HasModulePermission;

    public function save(Request $request)
    {
        $this->ensurePermission('TECHNICAL_APPROVAL', 'add', '1');
        // Validate required fields
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|integer',
            'rfq_no' => 'required|string|max:100',
            'technical_approval_description' => 'required|string|max:500',
            'technical_approval' => 'required|in:Yes,No',
        ]);

        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        // var_dump($_POST);die;

        // Lookup by vendor_id and rfq_no
        $row = DB::table('technical_approvals')
            ->where('vendor_id', $request->vendor_id)
            ->where('rfq_no', $request->rfq_no)
            ->first();

        $dataToSave = [
            'description'         => $request->technical_approval_description,
            'technical_approval'  => $request->technical_approval,
            'updated_at'          => now(),
        ];

        if ($row) {
            // Update if exists
            DB::table('technical_approvals')
                ->where('vendor_id', $request->vendor_id)
                ->where('rfq_no', $request->rfq_no)
                ->update($dataToSave);
        } else {
            // Insert if not exists
            $dataToSave['vendor_id'] = $request->vendor_id;
            $dataToSave['rfq_no']    = $request->rfq_no;
            $dataToSave['created_at'] = now();
            DB::table('technical_approvals')->insert($dataToSave);
        }

        return response()->json(['status' => true, 'message' => 'Technical approval saved successfully.']);
    }
}
