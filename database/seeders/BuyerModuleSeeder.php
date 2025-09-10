<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BuyerModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $modules = [
            ['id' => 27, 'module_name' => 'GENERATE NEW RFQ', 'module_for' => '1', 'module_slug' => 'GENERATE_NEW_RFQ', 'is_active' => 1, 'is_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'module_name' => 'GENERATE BULK RFQ', 'module_for' => '1', 'module_slug' => 'GENERATE_BULK_RFQ', 'is_active' => 1, 'is_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'module_name' => 'EDIT RFQ', 'module_for' => '1', 'module_slug' => 'EDIT_RFQ', 'is_active' => 1, 'is_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'module_name' => 'ACTIVE RFQS CIS', 'module_for' => '1', 'module_slug' => 'ACTIVE_RFQS_CIS', 'is_active' => 1, 'is_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 31, 'module_name' => 'TECHNICAL APPROVAL', 'module_for' => '1', 'module_slug' => 'TECHNICAL_APPROVAL', 'is_active' => 1, 'is_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'module_name' => 'TECHNICAL APPROVAL WITH PRICE', 'module_for' => '1', 'module_slug' => 'TECHNICAL_APPROVAL_WITH_PRICE', 'is_active' => 1, 'is_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'module_name' => 'AUCTION', 'module_for' => '1', 'module_slug' => 'AUCTION', 'is_active' => 1, 'is_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 34, 'module_name' => 'COUNTER OFFER RFQ', 'module_for' => '1', 'module_slug' => 'COUNTER_OFFER_RFQ', 'is_active' => 1, 'is_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 35, 'module_name' => 'TO GENERATE UNAPPROVE PO', 'module_for' => '1', 'module_slug' => 'TO_GENERATE_UNAPPROVE_PO', 'is_active' => 1, 'is_order' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 36, 'module_name' => 'TO CONFIRM ORDER', 'module_for' => '1', 'module_slug' => 'TO_CONFIRM_ORDER', 'is_active' => 1, 'is_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 37, 'module_name' => 'CANCEL ORDER', 'module_for' => '1', 'module_slug' => 'CANCEL_ORDER', 'is_active' => 1, 'is_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 38, 'module_name' => 'CLOSE RFQ', 'module_for' => '1', 'module_slug' => 'CLOSE_RFQ', 'is_active' => 1, 'is_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 39, 'module_name' => 'DRAFT RFQ', 'module_for' => '1', 'module_slug' => 'DRAFT_RFQ', 'is_active' => 1, 'is_order' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 40, 'module_name' => 'SCHEDULED RFQ', 'module_for' => '1', 'module_slug' => 'SCHEDULED_RFQ', 'is_active' => 1, 'is_order' => 14, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 41, 'module_name' => 'SENT RFQ', 'module_for' => '1', 'module_slug' => 'SENT_RFQ', 'is_active' => 1, 'is_order' => 15, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 42, 'module_name' => 'ORDERS CONFIRMED LISTING', 'module_for' => '1', 'module_slug' => 'ORDERS_CONFIRMED_LISTING', 'is_active' => 1, 'is_order' => 16, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 43, 'module_name' => 'UNAPPROVE PO LISTING', 'module_for' => '1', 'module_slug' => 'UNAPPROVE_PO_LISTING', 'is_active' => 1, 'is_order' => 17, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 44, 'module_name' => 'VENDORS SEARCH', 'module_for' => '1', 'module_slug' => 'VENDORS_SEARCH', 'is_active' => 1, 'is_order' => 18, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 45, 'module_name' => 'FAVOURITE VENDORS', 'module_for' => '1', 'module_slug' => 'FAVOURITE_VENDORS', 'is_active' => 1, 'is_order' => 19, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 46, 'module_name' => 'BLACKLISTED VENDORS', 'module_for' => '1', 'module_slug' => 'BLACKLISTED_VENDORS', 'is_active' => 1, 'is_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 47, 'module_name' => 'REPORTS - TOTAL RFQ CREATED', 'module_for' => '1', 'module_slug' => 'REPORTS_TOTAL_RFQ_CREATED', 'is_active' => 1, 'is_order' => 21, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 48, 'module_name' => 'REPORTS - ORDER CONFIRMATION SUMMARY', 'module_for' => '1', 'module_slug' => 'REPORTS_ORDER_CONFIRMATION_SUMMARY', 'is_active' => 1, 'is_order' => 22, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 49, 'module_name' => 'REPORTS - PRODUCT ORDERED DETAILS', 'module_for' => '1', 'module_slug' => 'REPORTS_PRODUCT_ORDERED_DETAILS', 'is_active' => 1, 'is_order' => 23, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 50, 'module_name' => 'REPORTS - VENDOR WISE ACTIVITY', 'module_for' => '1', 'module_slug' => 'REPORTS_VENDOR_WISE_ACTIVITY', 'is_active' => 1, 'is_order' => 24, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 51, 'module_name' => 'MY PROFILE', 'module_for' => '1', 'module_slug' => 'MY_PROFILE', 'is_active' => 1, 'is_order' => 25, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 52, 'module_name' => 'MANAGE USERS', 'module_for' => '1', 'module_slug' => 'MANAGE_USERS', 'is_active' => 1, 'is_order' => 26, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 53, 'module_name' => 'MANAGE ROLE', 'module_for' => '1', 'module_slug' => 'MANAGE_ROLE', 'is_active' => 1, 'is_order' => 27, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 54, 'module_name' => 'CHANGE PASSWORD', 'module_for' => '1', 'module_slug' => 'CHANGE_PASSWORD', 'is_active' => 1, 'is_order' => 28, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 55, 'module_name' => 'MESSAGE - INTERNAL', 'module_for' => '1', 'module_slug' => 'MESSAGE_INTERNAL', 'is_active' => 1, 'is_order' => 29, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 56, 'module_name' => 'MESSAGE - VENDORS', 'module_for' => '1', 'module_slug' => 'MESSAGE_VENDORS', 'is_active' => 1, 'is_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 57, 'module_name' => 'MESSAGE - RAPROCURE SUPPORT', 'module_for' => '1', 'module_slug' => 'MESSAGE_RAPROCURE_SUPPORT', 'is_active' => 1, 'is_order' => 31, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['id' => $module['id']],
                $module
            );
        }
    }
}

