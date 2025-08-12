<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleRouteAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('dashboard');
        }

        // Use Spatie to get the user's role
        $role = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->first() : null;

        // Define allowed route patterns for each role
        $roleRoutes = [
            'cashier' => [
                'pos*',
                'profile*',
                'settings*',
                'logout',
            ],
            'owner' => [
                'dashboard*',
                'sales*',
                'businesses*',
                'branches*',
                'employers*',
                'inventory*',
                'products*',
                'discounts*',
                'reports*',
                'profile*',
                'settings*',
                'logout',
                'chat*',
                'purchases*',
                'purchase-items*',
                'stock-transfers*',
                'items*',
                'categories*',
                'modifiers*',
                'tax-groups*',
                'tax-rates*',
                'customers*',
                'suppliers*',
                'devices*',
                'stock-adjustment*',
                'stock-items*',
            ],
            'administrator' => [
                'dashboard*',
                'sales*',
                'businesses*',
                'branches*',
                'employers*',
                'inventory*',
                'products*',
                'discounts*',
                'reports*',
                'profile*',
                'settings*',
                'logout',
                'chat*',
                'purchases*',
                'purchase-items*',
                'stock-transfers*',
                'items*',
                'categories*',
                'modifiers*',
                'tax-groups*',
                'tax-rates*',
                'customers*',
                'suppliers*',
                'devices*',
                'stock-adjustment*',
                'stock-items*', 
            ],
            'manager' => [
                'dashboard*',
                'sales*',
                'businesses*',
                'branches*',
                'employers*',
                'inventory*',
                'products*',
                'discounts*',
                'reports*',
                'profile*',
                'settings*',
                'logout',
                'chat*',
                'purchases*',
                'purchase-items*',
                'stock-transfers*',
                'items*',
                'categories*',
                'modifiers*',
                'tax-groups*',
                'tax-rates*',
                'customers*',
                'suppliers*',
                'devices*',
                'stock-adjustment*',
                'stock-items*',
            ],
            'admin' => ['*'], // admin can access everything
        ];

        // If admin, allow all
        if (isset($roleRoutes[$role]) && in_array('*', $roleRoutes[$role])) {
            return $next($request);
        }

        // Check if current route matches allowed patterns
        $allowed = false;
        if (isset($roleRoutes[$role])) {
            foreach ($roleRoutes[$role] as $pattern) {
                if ($request->is($pattern)) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            // Redirect to dashboard if not allowed
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
} 