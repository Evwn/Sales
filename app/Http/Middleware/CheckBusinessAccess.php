<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $businessId = $request->route('business') 
            ?? $request->route('business_id') 
            ?? $request->input('business_id');

        if (!$businessId) {
            return response()->json(['message' => 'Business ID is required'], 400);
        }

        if (!$user->canAccessBusiness($businessId)) {
            return response()->json(['message' => 'Unauthorized access to this business'], 403);
        }

        return $next($request);
    }
} 