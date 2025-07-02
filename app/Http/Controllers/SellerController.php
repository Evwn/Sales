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
use App\Mail\SellerAccountCreatedMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerAccountUpdatedMail;

class SellerController extends Controller
{
    public function __construct()
    {
        $user = auth()->user();
        if ($user && in_array($user->role->name ?? $user->role, ['seller', 'customer', 'supplier'])) {
            auth()->logout();
            redirect('/login')->send();
            exit;
        }
    }

    public function index(Business $business, Branch $branch)
    {
        $sellers = User::whereHas('roles', function ($q) {
                $q->where('name', 'seller');
            })
            ->where('business_id', $business->id)
            ->where('branch_id', $branch->id)
            ->with(['branch.business'])
            ->get();

        return Inertia::render('Sellers/Index', [
            'business' => $business,
            'branch' => $branch,
            'sellers' => $sellers,
            'userRole' => Auth::user()->getRoleNames()->first()
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
            'business_id' => $business->id,
            'branch_id' => $branch->id,
        ]);

        // Assign the seller role using Spatie
        $seller->assignRole('seller');

        // Send email verification
        $seller->sendEmailVerificationNotification();

        // Send professional welcome email
        Mail::to($seller->email)->send(new SellerAccountCreatedMail($seller, $branch, $business));

        return redirect()->route('sellers.index', [$business, $branch])
            ->with('success', 'Seller created successfully. An email has been sent for verification.');
    }

    public function show(Business $business, Branch $branch, User $seller)
    {
        if (!$seller->hasRole('seller') || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
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
        if (!$seller->hasRole('seller')) {
            abort(404);
        }

        // Get all businesses the current user can manage
        $user = Auth::user();
        $businesses = Business::with('branches')
            ->where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();

        return Inertia::render('Sellers/Edit', [
            'business' => $business,
            'branch' => $branch,
            'seller' => $seller->load('branch.business'),
            'businesses' => $businesses,
        ]);
    }

    public function update(Request $request, Business $business, Branch $branch, User $seller)
    {
        if (!$seller->hasRole('seller')) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $seller->id,
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^a-zA-Z\\d]).+$/'
            ],
            'branch_id' => 'required|exists:branches,id',
            'business_id' => 'required|exists:businesses,id',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one symbol.'
        ]);

        $changes = [];
        if ($seller->name !== $validated['name']) {
            $changes[] = 'Name changed';
        }
        if ($seller->email !== $validated['email']) {
            $changes[] = 'Email changed';
        }
        if ($seller->branch_id != $validated['branch_id']) {
            $changes[] = 'Branch changed';
        }
        if ($seller->business_id != $validated['business_id']) {
            $changes[] = 'Business changed';
        }
        if (!empty($validated['password'])) {
            $changes[] = 'Password changed';
        }

        $seller->name = $validated['name'];
        $seller->email = $validated['email'];
        $seller->branch_id = $validated['branch_id'];
        $seller->business_id = $validated['business_id'];
        if (!empty($validated['password'])) {
            $seller->password = Hash::make($validated['password']);
        }
        $seller->save();

        // Send custom email to seller about changes
        $branchModel = \App\Models\Branch::find($seller->branch_id);
        $businessModel = \App\Models\Business::find($seller->business_id);
        Mail::to($seller->email)->send(new SellerAccountUpdatedMail($seller, $branchModel, $businessModel, $changes));

        return redirect()->route('sellers.index', [$business, $branch])
            ->with('success', 'Seller updated successfully. The seller has been notified of the changes.');
    }

    public function destroy(Business $business, Branch $branch, User $seller)
    {
        if (!$seller->hasRole('seller') || $seller->business_id !== $business->id || $seller->branch_id !== $branch->id) {
            abort(404);
        }

        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('business_admin')) {
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
        $query = User::role('seller')
            ->whereHas('branch.business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('admins', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });

        // Get all branches for the businesses
        $branches = Branch::whereHas('business', function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('admins', function ($q2) use ($user) {
                  $q2->where('user_id', $user->id);
              });
        })->get();

        // If branch filter is provided
        if (request()->has('branch_id') && request('branch_id') !== 'all') {
            $query->where('branch_id', request('branch_id'));
        }

        $sellers = $query->with(['branch.business'])->get();

        return Inertia::render('Sellers/Index', [
            'sellers' => $sellers,
            'branches' => $branches,
            'userRole' => $user->getRoleNames()->first(),
            'selectedBranch' => request('branch_id', 'all')
        ]);
    }
} 