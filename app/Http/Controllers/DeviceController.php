<?php

namespace App\Http\Controllers;

use App\Models\PosDevice;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get all businesses where the user is the owner
        $ownedBusinessIds = \App\Models\Business::where('owner_id', $user->id)->pluck('id')->toArray();

        // Get all branches for those businesses
        $ownedBranchIds = \App\Models\Branch::whereIn('business_id', $ownedBusinessIds)->pluck('id')->toArray();

        // Devices for owned businesses or their branches
        $devices = \App\Models\PosDevice::with(['business', 'branch', 'registeredBy'])
            ->whereIn('business_id', $ownedBusinessIds)
            ->orWhereIn('branch_id', $ownedBranchIds)
            ->get();

        $branches = \App\Models\Branch::all();
        $businesses = \App\Models\Business::all();

        return Inertia::render('Devices', [
            'devices' => $devices,
            'branches' => $branches,
            'businesses' => $businesses,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'device_uuid' => 'required|string|max:255|unique:pos_devices,device_uuid',
            'branch_id' => 'required|exists:branches,id',
            'business_id' => 'required|exists:businesses,id',
        ]);
        $device = PosDevice::create([
            'device_uuid' => $validated['device_uuid'],
            'business_id' => $validated['business_id'],
            'branch_id' => $validated['branch_id'],
            'registered_by' => $user->id,
            'registered_at' => now(),
        ]);
        return redirect()->route('devices.index')->with('success', 'Device registered successfully!');
    }

    public function destroy(PosDevice $device)
    {
        try {
            $device->delete();
            return redirect()->route('devices.index')->with('success', 'Device removed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove device.');
        }
    }
} 