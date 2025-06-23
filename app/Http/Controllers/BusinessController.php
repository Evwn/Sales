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
        // Log all incoming request data
        \Log::info('Business Update Request', [
            'business_id' => $business->id,
            'all_request_data' => $request->all(),
            'files' => $request->allFiles(),
            'has_logo' => $request->hasFile('logo'),
            'has_tax_document' => $request->hasFile('tax_document'),
            'has_registration_document' => $request->hasFile('registration_document'),
        ]);

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

        // Log validated data
        \Log::info('Business Update Validated Data', [
            'validated_data' => $validated,
            'business_before_update' => $business->toArray(),
        ]);

        $business->fill($validated);
        $business->website = $request->input('website');
        $business->industry = $request->input('industry');

        // Log business data after fill
        \Log::info('Business Data After Fill', [
            'business_data' => $business->toArray(),
            'website_from_request' => $request->input('website'),
            'industry_from_request' => $request->input('industry'),
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            \Log::info('Processing Logo Upload', [
                'logo_file' => $request->file('logo')->getClientOriginalName(),
                'logo_size' => $request->file('logo')->getSize(),
            ]);
            
            // Delete old logo if exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
                \Log::info('Deleted old logo', ['old_path' => $business->logo_path]);
            }
            $logoPath = $request->file('logo')->store('business-logos', 'public');
            $business->logo_path = $logoPath;
            \Log::info('Logo uploaded successfully', ['new_path' => $logoPath]);
        }

        // Handle tax document upload
        if ($request->hasFile('tax_document')) {
            \Log::info('Processing Tax Document Upload', [
                'tax_file' => $request->file('tax_document')->getClientOriginalName(),
                'tax_size' => $request->file('tax_document')->getSize(),
            ]);
            
            // Delete old document if exists
            if ($business->tax_document_path) {
                Storage::disk('public')->delete($business->tax_document_path);
                \Log::info('Deleted old tax document', ['old_path' => $business->tax_document_path]);
            }
            $taxDocPath = $request->file('tax_document')->store('business-documents', 'public');
            $business->tax_document_path = $taxDocPath;
            \Log::info('Tax document uploaded successfully', ['new_path' => $taxDocPath]);
        }

        // Handle registration document upload
        if ($request->hasFile('registration_document')) {
            \Log::info('Processing Registration Document Upload', [
                'reg_file' => $request->file('registration_document')->getClientOriginalName(),
                'reg_size' => $request->file('registration_document')->getSize(),
            ]);
            
            // Delete old document if exists
            if ($business->registration_document_path) {
                Storage::disk('public')->delete($business->registration_document_path);
                \Log::info('Deleted old registration document', ['old_path' => $business->registration_document_path]);
            }
            $regDocPath = $request->file('registration_document')->store('business-documents', 'public');
            $business->registration_document_path = $regDocPath;
            \Log::info('Registration document uploaded successfully', ['new_path' => $regDocPath]);
        }

        // Handle terms and conditions upload
        if ($request->hasFile('terms_and_conditions')) {
            \Log::info('Processing Terms Upload', [
                'terms_file' => $request->file('terms_and_conditions')->getClientOriginalName(),
                'terms_size' => $request->file('terms_and_conditions')->getSize(),
            ]);
            
            // Delete old document if exists
            if ($business->terms_and_conditions) {
                Storage::disk('public')->delete($business->terms_and_conditions);
                \Log::info('Deleted old terms document', ['old_path' => $business->terms_and_conditions]);
            }
            $termsPath = $request->file('terms_and_conditions')->store('business-documents', 'public');
            $business->terms_and_conditions = $termsPath;
            \Log::info('Terms document uploaded successfully', ['new_path' => $termsPath]);
        }

        // Log business data before save
        \Log::info('Business Data Before Save', [
            'business_data' => $business->toArray(),
            'is_dirty' => $business->isDirty(),
            'dirty_attributes' => $business->getDirty(),
        ]);

        $business->save();

        // Log after save
        \Log::info('Business Update Completed', [
            'business_after_save' => $business->fresh()->toArray(),
            'was_saved' => true,
        ]);

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