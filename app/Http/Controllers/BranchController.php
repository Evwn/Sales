<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function all()
    {
        $user = Auth::user();
        
        // Get all businesses for the current user
        $businesses = Business::when($user->role === 'admin', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->get(['id', 'name']);

        if ($businesses->isEmpty()) {
            return Inertia::render('Branches/NoBusiness');
        }

        // Get branches for all businesses
        $branches = Branch::whereIn('business_id', $businesses->pluck('id'))
            ->with('business')
            ->get();

        return Inertia::render('Branches/Index', [
            'business' => null,
            'branches' => $branches,
            'businesses' => $businesses
        ]);
    }

    public function index(Business $business)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $user = Auth::user();
        
        // Get all businesses that belong to the current admin
        $businesses = Business::when($user->role === 'admin', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->get(['id', 'name']);

        return Inertia::render('Branches/Index', [
            'business' => $business,
            'branches' => $business->branches()->with('business')->get(),
            'businesses' => $businesses
        ]);
    }

    public function create(Business $business)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $user = Auth::user();
        
        // Get all businesses for the current user
        $businesses = Business::when($user->role === 'admin', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->get(['id', 'name']);

        // Get all sellers that belong to the user's businesses
        $sellers = User::whereHas('branch.business', function ($query) use ($user) {
                $query->when($user->role === 'admin', function ($q) use ($user) {
                    return $q->where('owner_id', $user->id);
                })
                ->when($user->role === 'owner', function ($q) use ($user) {
                    return $q->where('owner_id', $user->id);
                });
            })
            ->where('role', 'seller')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Branches/Create', [
            'business' => $business,
            'businesses' => $businesses,
            'sellers' => $sellers
        ]);
    }

    public function store(Request $request, Business $business)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'business_id' => 'required|exists:businesses,id',
            'seller_ids' => 'array',
            'seller_ids.*' => 'exists:users,id'
        ]);

        // Create the branch
        $branch = Branch::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'business_id' => $validated['business_id']
        ]);

        // Handle seller assignments
        if (isset($validated['seller_ids'])) {
            // First, remove sellers from their current branches
            User::whereIn('id', $validated['seller_ids'])
                ->update(['branch_id' => null]);

            // Then assign them to this branch
            User::whereIn('id', $validated['seller_ids'])
                ->update(['branch_id' => $branch->id]);
        }

        return redirect()->route('branches.index', $business)
            ->with('success', 'Branch created successfully.');
    }

    public function show(Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        return Inertia::render('Branches/Show', [
            'business' => $business,
            'branch' => $branch->load('business', 'sellers')
        ]);
    }

    public function edit(Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $user = Auth::user();
        
        // Get businesses that belong to the current admin
        $businesses = Business::when($user->role === 'admin', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->get(['id', 'name']);

        // Get sellers that belong to the current admin's businesses
        $sellers = User::where('role', 'seller')
            ->whereHas('branch.business', function ($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->get(['id', 'name', 'email']);

        return Inertia::render('Branches/Edit', [
            'business' => $business,
            'branch' => $branch->load('sellers'),
            'businesses' => $businesses,
            'sellers' => $sellers
        ]);
    }

    public function update(Request $request, Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'business_id' => 'required|exists:businesses,id',
            'seller_ids' => 'array',
            'seller_ids.*' => 'exists:users,id'
        ]);

        // Update branch details
        $branch->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'business_id' => $validated['business_id']
        ]);

        // Handle seller assignments
        if (isset($validated['seller_ids'])) {
            // First, remove sellers from their current branches
            User::whereIn('id', $validated['seller_ids'])
                ->update(['branch_id' => null]);

            // Then assign them to this branch
            User::whereIn('id', $validated['seller_ids'])
                ->update(['branch_id' => $branch->id]);
        }

        return redirect()->route('branches.index', $business)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        $branch->delete();
        return redirect()->route('branches.index', $business)
            ->with('success', 'Branch deleted successfully.');
    }
}