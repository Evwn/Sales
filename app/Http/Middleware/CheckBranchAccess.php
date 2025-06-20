<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $branch = $request->route('branch');

        // If no branch is specified, allow access
        if (!$branch) {
            return $next($request);
        }

        // Convert branch to integer if it's a string
        $branchId = is_string($branch) ? (int) $branch : $branch->id;

        // Admin has access to all branches
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Owner can access branches of their businesses
        if ($user->hasRole('owner')) {
            $hasAccess = $user->ownedBusinesses()
                ->whereHas('branches', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                })
                ->exists();

            if ($hasAccess) {
                return $next($request);
            }
        }

        // Seller can only access their assigned branch
        if ($user->hasRole('seller')) {
            $hasAccess = $user->branch_id === $branchId;

            if ($hasAccess) {
                return $next($request);
            }
        }

        // For Inertia requests, return a proper Inertia response
        if ($request->inertia()) {
            abort(403, 'Unauthorized access to this branch.');
        }

        abort(403, 'Unauthorized access to this branch.');
    }
} 