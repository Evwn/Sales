<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'logo' => 'nullable|image|max:1024', // max 1MB
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'registration_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
        ]);

        $business = new Business();
        $business->fill($validated);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('business-logos', 'public');
            $business->logo_path = $logoPath;
        }

        // Handle tax document upload
        if ($request->hasFile('tax_document')) {
            $taxDocPath = $request->file('tax_document')->store('business-documents', 'public');
            $business->tax_document_path = $taxDocPath;
        }

        // Handle registration document upload
        if ($request->hasFile('registration_document')) {
            $regDocPath = $request->file('registration_document')->store('business-documents', 'public');
            $business->registration_document_path = $regDocPath;
        }

        $business->owner_id = Auth::id();
        $business->save();

        return redirect()->route('businesses.index')
            ->with('success', 'Business created successfully.');
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
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'logo' => 'nullable|image|max:1024', // max 1MB
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'registration_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
        ]);

        $business->fill($validated);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $logoPath = $request->file('logo')->store('business-logos', 'public');
            $business->logo_path = $logoPath;
        }

        // Handle tax document upload
        if ($request->hasFile('tax_document')) {
            // Delete old document if exists
            if ($business->tax_document_path) {
                Storage::disk('public')->delete($business->tax_document_path);
            }
            $taxDocPath = $request->file('tax_document')->store('business-documents', 'public');
            $business->tax_document_path = $taxDocPath;
        }

        // Handle registration document upload
        if ($request->hasFile('registration_document')) {
            // Delete old document if exists
            if ($business->registration_document_path) {
                Storage::disk('public')->delete($business->registration_document_path);
            }
            $regDocPath = $request->file('registration_document')->store('business-documents', 'public');
            $business->registration_document_path = $regDocPath;
        }

        $business->save();

        return redirect()->route('businesses.index')
            ->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business)
    {
        // Delete associated files
        if ($business->logo_path) {
            Storage::disk('public')->delete($business->logo_path);
        }
        if ($business->tax_document_path) {
            Storage::disk('public')->delete($business->tax_document_path);
        }
        if ($business->registration_document_path) {
            Storage::disk('public')->delete($business->registration_document_path);
        }

        $business->delete();

        return redirect()->route('businesses.index')
            ->with('success', 'Business deleted successfully.');
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
                'role_id' => 1, // Using role_id 1 for admin role
                'business_id' => $business->id,
            ]);
        } else {
            // Update existing user's role and business
            $user->update([
                'role_id' => 1, // Using role_id 1 for admin role
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
                'role_id' => 3, // Using role_id 3 for seller role as default
                'business_id' => null
            ]);
        }

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Admin removed successfully.');
    }
} 