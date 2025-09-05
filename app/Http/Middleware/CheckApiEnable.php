<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckApiEnable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $path = $request->path();
            /***:- allow both calling indent and inventory  -:***/
            if (str_starts_with($path, 'buyer/inventory/search-products')) {
                return $next($request);
            }
            // If API is NOT enabled → Allow only buyer/inventory section
            if (!$user->is_api_enable) {
                if (str_starts_with($path, 'buyer/inventory')) {
                    return $next($request);
                }
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Unauthorized for API section'], 403);
                }
                return redirect()->to('/buyer/inventory');
            }
            // If API is enabled → Allow only buyer/api-indent section
            if ($user->is_api_enable) {
                if (str_starts_with($path, 'buyer/api-indent')) {
                    return $next($request);
                }
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Unauthorized for Inventory section'], 403);
                }
                return redirect()->to('/buyer/api-indent');
            }
        }
        return $next($request);
    }
}
