<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function all()
    {
        $user = Auth::user();
        
        // Get the first business for the current user
        $business = Business::when($user->role === 'admin', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->when($user->role === 'owner', function ($query) use ($user) {
                return $query->where('owner_id', $user->id);
            })
            ->first();

        if (!$business) {
            return Inertia::render('Branches/NoBusiness');
        }

        // Get branches for the business
        $branches = $business->branches()
            ->with('business')
            ->get();

        return Inertia::render('Branches/Index', [
            'business' => $business,
            'branches' => $branches
        ]);
    }

    public function index(Business $business)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        return Inertia::render('Branches/Index', [
            'business' => $business,
            'branches' => $business->branches()->with('business')->get()
        ]);
    }

    public function create(Business $business)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        return Inertia::render('Branches/Create', [
            'business' => $business
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
        ]);

        $branch = $business->branches()->create($validated);

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

        return Inertia::render('Branches/Edit', [
            'business' => $business,
            'branch' => $branch
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
        ]);

        $branch->update($validated);

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