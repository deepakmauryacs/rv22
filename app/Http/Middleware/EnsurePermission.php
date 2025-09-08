<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EnsurePermission
{
    /**
     * Map route prefixes to module slugs.
     *
     * @var array<string,string>
     */
    protected array $moduleMap = [
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
            $moduleSlug = $this->resolveModuleSlug($name);
            if ($moduleSlug) {
                $action = $this->resolveAction($name);
                $permissionType = $this->permissionMap[$action] ?? 'view';

                if (!checkPermission($moduleSlug, $permissionType, '3')) {
                    abort(403, 'Unauthorized');
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve module slug based on route name.
     */
    protected function resolveModuleSlug(string $routeName): ?string
    {
        foreach ($this->moduleMap as $prefix => $slug) {
            if (Str::startsWith($routeName, 'admin.' . $prefix)) {
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
