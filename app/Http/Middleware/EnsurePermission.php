<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnsurePermission
{
    /**
     * Map admin route prefixes to module slugs.
     *
     * @var array<string,string>
     */
    protected array $adminModuleMap = [
        'divisions' => 'PRODUCT_DIRECTORY',
        'verified-products' => 'ALL_VERIFIED_PRODUCTS',
        'product-approvals' => 'PRODUCTS_FOR_APPROVAL',
        'new-products' => 'NEW_PRODUCT_REQUEST',
        'edit-products' => 'EDIT_PRODUCT',
        'bulk-products' => 'EDIT_PRODUCT',
        'buyer' => 'BUYER_MODULE',
        'vendor' => 'VENDOR_MODULE',
        'advertisement' => 'ADVERTISEMENT_AND_MARKETING',
        'accounts.buyer' => 'BUYERS_ACCOUNTS',
        'accounts.vendor' => 'VENDORS_ACCOUNTS',
        'plan' => 'PLAN_MODULE',
        'reports.product-division-category' => 'DIVISION_AND_CATEGORY_WISE',
        'reports.buyer-activity' => 'BUYER_ACTIVITY_REPORTS',
        'vendor-activity-report' => 'VENDOR_ACTIVITY_REPORTS',
        'users' => 'ADMIN_USERS',
        'user-roles' => 'MANAGE_ROLE',
        'help_support' => 'HELP_AND_SUPPORT',
        'password' => 'CHANGE_PASSWORD',
    ];

    /**
     * Map buyer route prefixes to module slugs.
     *
     * @var array<string,string>
     */
    protected array $buyerModuleMap = [
        'rfq.active-rfq' => 'ACTIVE_RFQS_CIS',
        'rfq.draft-rfq' => 'DRAFT_RFQ',
        'rfq.scheduled-rfq' => 'SCHEDULED_RFQ',
        'rfq.sent-rfq' => 'SENT_RFQ',
        'rfq.compose' => 'GENERATE_NEW_RFQ',
        'rfq.close' => 'CLOSE_RFQ',
        'rfq.edit' => 'EDIT_RFQ',
        'rfq.update' => 'EDIT_RFQ',
        'rfq.counter-offer' => 'COUNTER_OFFER_RFQ',
        'rfq.save-counter-offer' => 'COUNTER_OFFER_RFQ',
        'rfq.quotation-received' => 'COUNTER_OFFER_RFQ',
        'rfq.cis.technical-approval' => 'TECHNICAL_APPROVAL',
        'rfq.cis-sheet' => 'ACTIVE_RFQS_CIS',
        'rfq.cis.last-cis-po' => 'TECHNICAL_APPROVAL_WITH_PRICE',
        'rfq.order-confirmed.cancel' => 'CANCEL_ORDER',
        'rfq.order-confirmed' => 'ORDERS_CONFIRMED_LISTING',
        'unapproved-orders' => 'UNAPPROVE_PO_LISTING',
        'unapproved-orders.generatePO' => 'TO_GENERATE_UNAPPROVE_PO',
        'unapproved-orders.approvePO' => 'TO_CONFIRM_ORDER',
        'unapproved-orders.deletePO' => 'CANCEL_ORDER',
        'search-vendor' => 'VENDORS_SEARCH',
        'vendor.favourite' => 'FAVOURITE_VENDORS',
        'vendor.blacklist' => 'BLACKLISTED_VENDORS',
        'auction' => 'AUCTION',
        'forward-auction' => 'AUCTION',
        'profile' => 'MY_PROFILE',
        'user-management.users' => 'MANAGE_USERS',
        'role-permission.roles' => 'MANAGE_ROLE',
        'setting.change-password' => 'CHANGE_PASSWORD',
    ];

    /**
     * Map route action to permission type.
     *
     * @var array<string,string>
     */
    protected array $permissionMap = [
        'index' => 'view',
        'show' => 'view',
        'create' => 'add',
        'store' => 'add',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
        'delete' => 'delete',
        'bulkDelete' => 'delete',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $name = $route?->getName();

        if ($name) {
            $moduleFor = null;
            $moduleSlug = $this->resolveModuleSlug($name, $moduleFor);
            if ($moduleSlug && $moduleFor) {
                $action = $this->resolveAction($name);
                $permissionType = $this->permissionMap[$action] ?? 'view';

                if (!checkPermission($moduleSlug, $permissionType, $moduleFor)) {
                    abort(403, 'Unauthorized');
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve module slug based on route name.
     */
    protected function resolveModuleSlug(string $routeName, ?string &$moduleFor = null): ?string
    {
        foreach ($this->adminModuleMap as $prefix => $slug) {
            if (Str::startsWith($routeName, 'admin.' . $prefix)) {
                $moduleFor = '3';
                return $slug;
            }
        }

        foreach ($this->buyerModuleMap as $prefix => $slug) {
            if (Str::startsWith($routeName, 'buyer.' . $prefix)) {
                $moduleFor = '1';
                return $slug;
            }
        }

        return null;
    }

    /**
     * Resolve the action segment from the route name.
     */
    protected function resolveAction(string $routeName): string
    {
        $parts = explode('.', $routeName);
        return end($parts);
    }
}
