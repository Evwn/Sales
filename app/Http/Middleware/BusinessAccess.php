<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class BusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $businessId = $request->route('business')?->id;

        // If no business ID is provided, allow access
        if (!$businessId) {
            return $next($request);
        }

        // Check if user has access to the business
        if (!$user->canAccessBusiness($businessId)) {
            if ($request->inertia()) {
                return Inertia::location(route('businesses.index'));
            }
            return redirect()->route('businesses.index')->with('error', 'Unauthorized access to this business.');
        }

        return $next($request);
    }
} 