<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch;

class LocationController extends Controller
{
    /**
     * Display the locations management page
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all locations for the current user
        $locations = Location::with('locationType', 'business')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        // Get all location types
        $locationTypes = LocationType::orderBy('name')->get();

        // Get all businesses for the dropdown
        $businesses = \App\Models\Business::orderBy('name')->get();

        // Get all branches for the dropdown
        $branches = Branch::orderBy('name')->get();

        return Inertia::render('settings/Locations', [
            'locations' => $locations,
            'locationTypes' => $locationTypes,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location_type_id' => 'required|exists:location_types,id',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'boolean',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'location_type_id' => $request->location_type_id,
            'business_id' => $request->business_id ?: null,
            'branch_id' => $request->branch_id ?: null,
            'address' => $request->address,
            'phone' => $request->phone,
            'status' => $request->status ? 1 : 0,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->route('settings.locations')
            ->with('success', 'Location created successfully');
    }

    /**
     * Update the specified location
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location_type_id' => 'required|exists:location_types,id',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'boolean',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $location->update([
            'name' => $request->name,
            'location_type_id' => $request->location_type_id,
            'business_id' => $request->business_id ?: null,
            'branch_id' => $request->branch_id ?: null,
            'address' => $request->address,
            'phone' => $request->phone,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('settings.locations')
            ->with('success', 'Location updated successfully');
    }

    /**
     * Remove the specified location
     */
    public function destroy(Location $location)
    {
        // Allow deleting any location (user-specific locations)

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully',
        ]);
    }

    /**
     * Get locations for API (for filtering/searching)
     */
    public function getLocations(Request $request)
    {
        $query = Location::with('locationType');

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Apply type filter
        if ($request->has('type') && $request->type) {
            $query->where('location_type_id', $request->type);
        }

        $locations = $query->orderBy('name')->get();

        return response()->json($locations);
    }

    public function create()
    {
        $locationTypes = \App\Models\LocationType::orderBy('name')->get();
        $businesses = \App\Models\Business::orderBy('name')->get();
        $branches = \App\Models\Branch::orderBy('name')->get();
        return Inertia::render('settings/LocationsCreate', [
            'locationTypes' => $locationTypes,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function edit($id)
    {
        $location = \App\Models\Location::findOrFail($id);
        $locationTypes = \App\Models\LocationType::orderBy('name')->get();
        $businesses = \App\Models\Business::orderBy('name')->get();
        $branches = \App\Models\Branch::orderBy('name')->get();
        return Inertia::render('settings/LocationsEdit', [
            'location' => $location,
            'locationTypes' => $locationTypes,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }
} 