<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $branchId = $request->route('branch') 
            ?? $request->route('branch_id') 
            ?? $request->input('branch_id');

        if (!$branchId) {
            return response()->json(['message' => 'Branch ID is required'], 400);
        }

        if (!$user->canAccessBranch($branchId)) {
            return response()->json(['message' => 'Unauthorized access to this branch'], 403);
        }

        return $next($request);
    }
} 