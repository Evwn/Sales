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
        if ($user->hasRole('admin')) {
            $businesses = Business::all();
        } else {
            $businesses = Business::where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->get();
        }
        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
        ]);
    }

    public function create()
    {
        // Fetch all users with the 'owner' role
        $owners = \App\Models\User::role('owner')->get(['id', 'name', 'email']);
        return Inertia::render('Businesses/Create', [
            'owners' => $owners
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $rules = [
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
            'logo' => 'nullable|image|max:1024',
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'registration_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'website' => 'nullable|string|max:255',
            'terms_and_conditions' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
        if ($user->hasRole('admin')) {
            $rules['owner_id'] = 'required|exists:users,id';
        }
        $validated = $request->validate($rules);

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

        // Set owner_id
        if ($user->hasRole('admin')) {
            $business->owner_id = $validated['owner_id'];
        } else {
            $business->owner_id = $user->id;
        }
        $business->save();

        // Send email to business contact (the email field in the form)
        try {
            \Mail::to($business->email)->send(new \App\Mail\BusinessCreatedMail($business));
            // Also send to the owner if not already sent
            if ($business->owner && $business->owner->email !== $business->email) {
                \Mail::to($business->owner->email)->send(new \App\Mail\BusinessCreatedMail($business));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send business created email: ' . $e->getMessage());
        }

        if ($user->hasRole('admin')) {
            return redirect('/admin/businesses')->with('success', 'Business created successfully.');
        } else {
            return redirect()->route('businesses.index')->with('success', 'Business created successfully.');
        }
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
        $user = auth()->user();
        $oldData = $business->toArray();
        
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

        // Detect which key fields were updated
        $updatedItems = [];
        if (isset($oldData['logo_path']) && $oldData['logo_path'] != $business->logo_path) {
            $updatedItems[] = 'Business Logo';
        }
        if (isset($oldData['tax_document_path']) && $oldData['tax_document_path'] != $business->tax_document_path) {
            $updatedItems[] = 'Tax Document';
        }
        if (isset($oldData['registration_document_path']) && $oldData['registration_document_path'] != $business->registration_document_path) {
            $updatedItems[] = 'Business Registration Document';
        }
        if (isset($oldData['terms_and_conditions']) && $oldData['terms_and_conditions'] != $business->terms_and_conditions) {
            $updatedItems[] = 'Terms and Conditions Document';
        }

        // Send email to owner if owner is updating their own business
        if ($user && $user->hasRole('owner')) {
            if (!empty($updatedItems)) {
                try {
                    \Mail::to($business->email)->send(new \App\Mail\BusinessEditedMail($business, $updatedItems, $user));
                    if ($business->owner && $business->owner->email !== $business->email) {
                        \Mail::to($business->owner->email)->send(new \App\Mail\BusinessEditedMail($business, $updatedItems, $user));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send business edited email: ' . $e->getMessage());
                }
            }
        }

        // If admin, send email and redirect to admin businesses
        if ($user && $user->hasRole('admin')) {
            if (!empty($updatedItems)) {
                try {
                    \Mail::to($business->email)->send(new \App\Mail\BusinessEditedMail($business, $updatedItems, $user));
                    if ($business->owner && $business->owner->email !== $business->email) {
                        \Mail::to($business->owner->email)->send(new \App\Mail\BusinessEditedMail($business, $updatedItems, $user));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send business edited email: ' . $e->getMessage());
                }
            }
            return redirect('/admin/businesses')->with('success', 'Business updated successfully.');
        }

        return back()->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business)
    {
        // Soft delete all branches
        $deletedBranches = [];
        foreach ($business->branches as $branch) {
            $deletedBranches[] = $branch->name;
            $branch->delete();
        }
        // Soft delete the business
        $businessName = $business->name;
        $business->delete();

        // Send email to owner and business contact
        try {
            \Mail::to($business->email)->send(new \App\Mail\BusinessDeletedMail($businessName, $deletedBranches, auth()->user()));
            if ($business->owner && $business->owner->email !== $business->email) {
                \Mail::to($business->owner->email)->send(new \App\Mail\BusinessDeletedMail($businessName, $deletedBranches, auth()->user()));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send business deleted email: ' . $e->getMessage());
        }

        // If the request expects JSON (axios/fetch), return JSON for SweetAlert
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Business and all its branches deleted successfully.']);
        }

        // Redirect based on user role
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return redirect('/admin/businesses')->with('success', 'Business and all its branches deleted successfully.');
        } else {
            return redirect()->route('businesses.index')->with('success', 'Business and all its branches deleted successfully.');
        }
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
        $business->admins()->detach($user->id);
        return back()->with('success', 'Admin removed successfully.');
    }

    // Admin methods for viewing all businesses
    public function adminIndex()
    {
        $this->checkAdminAccess();
        
        $businesses = Business::with(['owner', 'branches', 'admins'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($business) {
                return [
                    'id' => $business->id,
                    'name' => $business->name,
                    'description' => $business->description,
                    'phone' => $business->phone,
                    'email' => $business->email,
                    'tax_number' => $business->tax_number,
                    'registration_number' => $business->registration_number,
                    'industry' => $business->industry,
                    'address' => $business->address,
                    'city' => $business->city,
                    'state' => $business->state,
                    'country' => $business->country,
                    'postal_code' => $business->postal_code,
                    'website' => $business->website,
                    'logo_path' => $business->logo_path,
                    'tax_document_path' => $business->tax_document_path,
                    'registration_document_path' => $business->registration_document_path,
                    'terms_and_conditions' => $business->terms_and_conditions,
                    'owner' => $business->owner ? [
                        'id' => $business->owner->id,
                        'name' => $business->owner->name,
                        'email' => $business->owner->email,
                    ] : null,
                    'branches_count' => $business->branches->count(),
                    'admins_count' => $business->admins->count(),
                    'created_at' => $business->created_at,
                    'updated_at' => $business->updated_at,
                ];
            });

        return Inertia::render('Admin/Businesses/Index', [
            'businesses' => $businesses,
        ]);
    }

    public function adminShow(Business $business)
    {
        $this->checkAdminAccess();
        
        $business->load(['owner', 'branches.sellers', 'admins', 'branches.products']);
        
        return Inertia::render('Admin/Businesses/Show', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'description' => $business->description,
                'phone' => $business->phone,
                'email' => $business->email,
                'tax_number' => $business->tax_number,
                'registration_number' => $business->registration_number,
                'industry' => $business->industry,
                'address' => $business->address,
                'city' => $business->city,
                'state' => $business->state,
                'country' => $business->country,
                'postal_code' => $business->postal_code,
                'website' => $business->website,
                'logo_path' => $business->logo_path,
                'tax_document_path' => $business->tax_document_path,
                'registration_document_path' => $business->registration_document_path,
                'terms_and_conditions' => $business->terms_and_conditions,
                'owner' => $business->owner ? [
                    'id' => $business->owner->id,
                    'name' => $business->owner->name,
                    'email' => $business->owner->email,
                    'created_at' => $business->owner->created_at,
                ] : null,
                'branches' => $business->branches->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'address' => $branch->address,
                        'phone' => $branch->phone,
                        'email' => $branch->email,
                        'sellers_count' => $branch->sellers->count(),
                        'products_count' => $branch->products->count(),
                        'created_at' => $branch->created_at,
                    ];
                }),
                'admins' => $business->admins->map(function ($admin) {
                    return [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'created_at' => $admin->created_at,
                    ];
                }),
                'created_at' => $business->created_at,
                'updated_at' => $business->updated_at,
            ],
        ]);
    }

    private function checkAdminAccess()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Access denied. Admin role required.');
        }
    }

    public function uploadDocument(Request $request, Business $business)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'document_type' => 'required|in:tax_document,registration_document,terms_and_conditions',
            'file' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $documentType = $request->input('document_type');
        $file = $request->file('file');
        
        // Delete old document if exists
        $oldPath = $business->{$documentType . '_path'};
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Store new document
        $path = $file->store('business-documents', 'public');
        $business->{$documentType . '_path'} = $path;
        $business->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'path' => $path,
        ]);
    }

    public function deleteDocument(Request $request, Business $business, $documentType)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'document_type' => 'required|in:tax_document,registration_document,terms_and_conditions',
        ]);
        
        $path = $business->{$documentType . '_path'};
        
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        
        $business->{$documentType . '_path'} = null;
        $business->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully',
        ]);
    }
} 