<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LocationType;
use Illuminate\Support\Facades\Auth;

class LocationTypeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:location_types,name',
        ]);
        $locationType = LocationType::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
        ]);
        return response()->json(['locationType' => $locationType], 201);
    }
} 