<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class BusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->route('business');
        $user = $request->user();

        // Admin can access all businesses
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Owner can only access their own businesses
        if ($user->role === 'owner' && $business->owner_id === $user->id) {
            return $next($request);
        }

        // Seller can only access businesses they are assigned to
        if ($user->role === 'seller') {
            $hasAccess = $user->branches()
                ->whereHas('business', function ($query) use ($business) {
                    $query->where('id', $business->id);
                })
                ->exists();

            if ($hasAccess) {
                return $next($request);
            }
        }

        // For Inertia requests, return a redirect response
        if ($request->inertia()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access to this business.');
        }

        // For regular requests, return a 403 response
        return response()->json(['message' => 'Unauthorized access to this business.'], 403);
    }
} 