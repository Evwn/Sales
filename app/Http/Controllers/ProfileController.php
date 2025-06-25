<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ProfileChangedNotification;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => Auth::user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => Auth::user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $oldEmail = $user->email;
        $oldName = $user->name;
        $oldLogo = $user->logo_url;

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
            $changes['logo_url'] = ['old' => $oldLogo, 'new' => $user->logo_url];
        }

        $user->save();

        // If user is a business owner, update business details
        if (isset($user->role) && $user->role === 'owner' && $user->business) {
            $user->business->update([
                'name' => $validated['business_name'] ?? $user->business->name,
                'description' => $validated['business_description'] ?? $user->business->description,
            ]);
        }

        // Send notification if there are changes
        if (!empty($changes)) {
            $user->notify(new ProfileChangedNotification($changes));
        }

        // If email was changed, log out the user and redirect to login with a message
        if (isset($changes['email'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return Redirect::route('login')->with('status', 'Your email was changed. Please log in again.');
        }

        return Redirect::route('profile.edit')->with('status', 'Profile updated successfully.')->with('user', [
            'name' => $user->name,
            'email' => $user->email,
            'logo_url' => $user->logo_url,
        ]);
    }

    public function destroy(): RedirectResponse
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();

        return Redirect::to('/');
    }
} 