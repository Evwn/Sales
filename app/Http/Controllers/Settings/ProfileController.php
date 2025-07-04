<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ProfileChangedNotification;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $business = $user->business;
        $branch = $user->branch;

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'business' => $business ? [
                    'id' => $business->id,
                    'name' => $business->name,
                    'description' => $business->description,
                ] : null,
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $oldEmail = $user->email;
        $oldName = $user->name;
        $oldLogo = 'oldLogo';

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($user->logo_url) {
                $oldPath = str_replace('/storage/', '', $user->logo_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $user->logo_url = Storage::url($path);
        }

        // Update user profile
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $changes = [];
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $changes['email'] = ['old' => $oldEmail, 'new' => $user->email];
        }
        if ($user->isDirty('name')) {
            $changes['name'] = ['old' => $oldName, 'new' => $user->name];
        }
        if ($user->isDirty('logo_url')) {
            $changes['logo_url'] = ['old' => 'oldLogo', 'new' => 'newLogo'];
        }

        $user->save();

        // If user is a business owner, update business details
        if ($user->role === 'owner' && $user->business) {
            $user->business->update([
                'name' => $validated['business_name'] ?? $user->business->name,
                'description' => $validated['business_description'] ?? $user->business->description,
            ]);
        }

        // Send notification if there are changes
        if (!empty($changes)) {
            $user->notify(new ProfileChangedNotification($changes));
        }

        return back()->with('status', 'Profile updated successfully.')->with('user', [
            'name' => $user->name,
            'email' => $user->email,
            'logo_url' => $user->logo_url,
        ]);
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // If user is a business owner, handle business deletion
        if ($user->role === 'owner' && $user->business) {
            $user->business->delete();
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
