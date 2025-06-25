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
        $businesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get();
        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
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
            'website' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $business = new Business();
        $business->fill($validated);
        $business->website = $request->input('website');
        $business->industry = $request->input('industry');

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

        // Handle terms and conditions upload
        if ($request->hasFile('terms_and_conditions')) {
            $termsPath = $request->file('terms_and_conditions')->store('business-documents', 'public');
            $business->terms_and_conditions = $termsPath;
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|email|max:255',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'country' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'logo' => 'nullable|image|max:1024', // max 1MB
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'registration_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'website' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $business->fill($validated);
        $business->website = $request->input('website');
        $business->industry = $request->input('industry');

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

        // Handle terms and conditions upload
        if ($request->hasFile('terms_and_conditions')) {
            // Delete old document if exists
            if ($business->terms_and_conditions) {
                Storage::disk('public')->delete($business->terms_and_conditions);
            }
            $termsPath = $request->file('terms_and_conditions')->store('business-documents', 'public');
            $business->terms_and_conditions = $termsPath;
        }

        $business->save();

        return back()->with('success', 'Business updated successfully.');
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
                'business_id' => $business->id,
            ]);
            $user->assignRole('admin');
        } else {
            // Update existing user's business
            $user->update([
                'business_id' => $business->id,
            ]);
            if (!$user->hasRole('admin')) {
                $user->assignRole('admin');
            }
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
                'business_id' => null
            ]);
            $user->syncRoles('seller');
        }

        return redirect()->route('businesses.show', $business)
            ->with('success', 'Admin removed successfully.');
    }
} 