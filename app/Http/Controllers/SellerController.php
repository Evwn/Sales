<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SellerController extends Controller
{
    public function index(Business $business, Branch $branch)
    {
        $sellers = User::where('role', 'seller')
            ->where('business_id', $business->id)
            ->where('branch_id', $branch->id)
            ->get();

        return Inertia::render('Sellers/Index', [
            'business' => $business,
            'branch' => $branch,
            'sellers' => $sellers
        ]);
    }

    public function create(Business $business, Branch $branch)
    {
        return Inertia::render('Sellers/Create', [
            'business' => $business,
            'branch' => $branch
        ]);
    }

    public function store(Request $request, Business $business, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $seller = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
            'business_id' => $business->id,
            'branch_id' => $branch->id,
        ]);

        return redirect()->route('sellers.index', [$business, $branch])
            ->with('success', 'Seller created successfully.');
    }

    public function show(Business $business, Branch $branch, User $seller)
    {
        if ($seller->role !== 'seller' || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
            abort(404);
        }

        return Inertia::render('Sellers/Show', [
            'business' => $business,
            'branch' => $branch,
            'seller' => $seller
        ]);
    }

    public function edit(Business $business, Branch $branch, User $seller)
    {
        if ($seller->role !== 'seller' || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
            abort(404);
        }

        return Inertia::render('Sellers/Edit', [
            'business' => $business,
            'branch' => $branch,
            'seller' => $seller
        ]);
    }

    public function update(Request $request, Business $business, Branch $branch, User $seller)
    {
        if ($seller->role !== 'seller' || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $seller->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $seller->name = $validated['name'];
        $seller->email = $validated['email'];
        
        if (isset($validated['password'])) {
            $seller->password = Hash::make($validated['password']);
        }

        $seller->save();

        return redirect()->route('sellers.index', [$business, $branch])
            ->with('success', 'Seller updated successfully.');
    }

    public function destroy(Business $business, Branch $branch, User $seller)
    {
        if ($seller->role !== 'seller' || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
            abort(404);
        }

        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'business_admin') {
            abort(403, 'Unauthorized action.');
        }

        $seller->delete();

        return redirect()->route('sellers.index', [$business, $branch])
            ->with('success', 'Seller deleted successfully.');
    }

    public function getBranches(Business $business)
    {
        // Check if user has access to this business
        if (!request()->user()->isAdmin()) {
            $hasAccess = request()->user()->ownedBusinesses()->where('id', $business->id)->exists()
                || request()->user()->managedBusinesses()->where('id', $business->id)->exists();
            
            if (!$hasAccess) {
                abort(403);
            }
        }

        return response()->json([
            'branches' => $business->branches,
        ]);
    }

    public function all()
    {
        $user = Auth::user();
        
        // Get the user's business if they're not an admin
        $business = null;
        if ($user->role !== 'super_admin') {
            $business = Business::where('owner_id', $user->id)->first();
            
            if (!$business) {
                return redirect()->route('businesses.create')
                    ->with('error', 'You need to create a business first.');
            }
        }
        
        $sellers = User::where('role', 'seller')
            ->with(['branch.business'])
            ->when($user->role === 'admin', function ($query) use ($user) {
                // Admin is treated as a business owner
                return $query->whereHas('branch.business', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                });
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                // Owner can only see sellers in their businesses
                return $query->whereHas('branch.business', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                });
            })
            ->when($user->role === 'seller', function ($query) use ($user) {
                // Seller can only see themselves
                return $query->where('id', $user->id);
            })
            ->get();

        // Get branches for the business if it exists
        $branches = [];
        if ($business) {
            $branches = $business->branches;
        }

        return Inertia::render('Sellers/Index', [
            'business' => $business,
            'branch' => null,
            'sellers' => $sellers,
            'branches' => $branches,
            'userRole' => $user->role
        ]);
    }
} 