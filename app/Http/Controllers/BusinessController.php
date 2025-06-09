<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class BusinessController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $businesses = Business::with('owner')
            ->when($user->role === 'admin', function ($query) use ($user) {
                // Admin is treated as a business owner
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                // Owner can only see their own businesses
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'seller', function ($query) use ($user) {
                // Seller can only see businesses they are assigned to
                return $query->whereHas('branches', function ($q) use ($user) {
                    $q->whereHas('sellers', function ($q) use ($user) {
                        $q->where('id', $user->id);
                    });
                });
            })
            ->get();

        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses
        ]);
    }

    public function create()
    {
        return Inertia::render('Businesses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $business = Business::create([
            ...$validated,
            'owner_id' => Auth::id(),
        ]);

        return redirect()->route('businesses.show', $business);
    }

    public function show(Business $business)
    {
        return Inertia::render('Businesses/Show', [
            'business' => $business->load(['owner', 'branches', 'admins'])
        ]);
    }

    public function edit(Business $business)
    {
        return Inertia::render('Businesses/Edit', [
            'business' => $business
        ]);
    }

    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $business->update($validated);

        return redirect()->route('businesses.show', $business);
    }

    public function destroy(Business $business)
    {
        $business->delete();

        return redirect()->route('businesses.index');
    }

    public function addAdmin(Request $request, Business $business)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Check if user exists
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user) {
            // Create new user
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'admin',
                'business_id' => $business->id,
            ]);
        } else {
            // Update existing user's role and business
            $user->update([
                'role' => 'admin',
                'business_id' => $business->id,
            ]);
        }

        // Attach as admin if not already
        if (!$business->admins()->where('user_id', $user->id)->exists()) {
            $business->admins()->attach($user->id);
        }

        return redirect()->route('businesses.show', $business)->with('success', 'Admin added successfully.');
    }

    public function removeAdmin(Business $business, User $user)
    {
        // Check if user is an admin of this business
        if (!$business->admins()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is not an admin of this business.']);
        }

        // Remove admin relationship
        $business->admins()->detach($user->id);

        // Update user's role and business if they're not an admin of any other business
        if (!$user->managedBusinesses()->exists()) {
            $user->update([
                'role' => 'user',
                'business_id' => null
            ]);
        }

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Admin removed successfully.');
    }
} 