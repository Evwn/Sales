<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeClockEntry;
use App\Models\Shift;

class TimeClockController extends Controller
{
    // ... existing code ...

    public function clockIn(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string',
            'pin_code' => 'required|digits:4',
        ]);
        $device = \App\Models\PosDevice::where('device_uuid', $request->device_uuid)->firstOrFail();
        $user = \App\Models\User::where('pin_code', $request->pin_code)
            ->where('business_id', $device->business_id)
            ->where('branch_id', $device->branch_id)
            ->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid PIN or clock in failed.'], 403);
        }
        // Optionally, log in the user
        auth()->login($user);
        $alreadyClockedIn = \App\Models\TimeClockEntry::where('user_id', $user->id)
            ->where('branch_id', $device->branch_id)
            ->whereNull('clock_out')
            ->exists();
        if ($alreadyClockedIn) {
            return response()->json(['error' => 'You are already clocked in.'], 403);
        }
        $entry = \App\Models\TimeClockEntry::create([
            'user_id' => $user->id,
            'branch_id' => $device->branch_id,
            'clock_in' => now(),
            'shift_id' => $request->shift_id ?? null,
        ]);
        return response()->json(['success' => true, 'entry_id' => $entry->id]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string',
        ]);
        $device = \App\Models\PosDevice::where('device_uuid', $request->device_uuid)->firstOrFail();
        $entry = \App\Models\TimeClockEntry::where('user_id', auth()->id())
            ->where('branch_id', $device->branch_id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
        if (!$entry) {
            return response()->json(['error' => 'You are not clocked in.'], 403);
        }
        // Prevent clock out if there is an open shift for this branch
        $openShift = \App\Models\Shift::where('branch_id', $device->branch_id)
            ->whereNull('closed_at')
            ->first();
        if ($openShift) {
            return response()->json(['error' => 'You must close your shift before clocking out.'], 403);
        }
        $entry->update(['clock_out' => now()]);
        return response()->json(['success' => true]);
    }

    // ... existing code ...
}