<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PosDevice;
use App\Models\TimeClockEntry;
use App\Models\User;
use Inertia\Inertia;


class PosLoginController extends Controller
{
    public function login(Request $request)
    { 
        $request->validate([
            'pin_code' => 'required|digits:4',
            'device_uuid' => 'required|string',
        ]);
        $device = PosDevice::where('device_uuid', $request->device_uuid)->first();
        if (!$device) {
            return back()->withErrors(['error' => 'Device not registered.']);
        }
        if ($device->is_disabled) {
            return back()->withErrors(['error' => 'This device has been disabled due to too many failed attempts. Please contact your administrator.']);
        }
        $user = User::where('pin_code', $request->pin_code)
            ->where('business_id', $device->business_id)
            ->where('branch_id', $device->branch_id)
            ->first();
        if (!$user) {
            $device->attempts += 1;
            if ($device->attempts >= 3) {
                $device->is_disabled = true;
            }
            $device->save();
            $remaining = max(0, 3 - $device->attempts);
            $msg = $device->is_disabled
                ? 'This device has been disabled due to too many failed attempts. Please contact your administrator.'
                : ($remaining > 0
                    ? 'Invalid PIN. You have ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.'
                    : 'Invalid PIN.');
            return back()->withErrors(['error' => $msg]);
        }
        // Success: reset attempts
        $device->attempts = 0;
        $device->save();
        Auth::login($user);
        session(['pos_login' => true]); // Set POS session flag
        session(['device_uuid' => $request->device_uuid]); // Store device UUID in session
        // Create time clock entry if not already clocked in
        $alreadyClockedIn = TimeClockEntry::where('user_id', $user->id)
            ->where('branch_id', $device->branch_id)
            ->whereNull('clock_out')
            ->exists();
        if (!$alreadyClockedIn) {
            TimeClockEntry::create([
                'user_id' => $user->id,
                'branch_id' => $device->branch_id,
                'clock_in' => now(),
            ]);
        }
        Inertia::clearHistory();
        return redirect('/pos/dashboard');
    }

    public function logout(Request $request)
    {   
         $user = Auth::user();
        if ($user && $user->branch_id) {
            $openClock = \App\Models\TimeClockEntry::where('user_id', $user->id)
                ->where('branch_id', $user->branch_id)
                ->whereNull('clock_out')
                ->first();
            if ($openClock) {
                $openClock->clock_out = now();
                $openClock->save();
            }
            // Close all open shifts for this branch
            \App\Models\Shift::where('branch_id', $user->branch_id)
                ->whereNull('closed_at')
                ->update(['closed_at' => now()]);
        }
        Auth::logout();
        $request->session()->forget('pos_login');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Inertia::clearHistory();
        return redirect('/pos');
    }
}