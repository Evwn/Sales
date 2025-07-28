<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'short_code' => 'required|string|max:10',
            'description' => 'nullable|string|max:255',
        ]);
        $data['owner_id'] = auth()->id();
        try {
            $unit = Unit::create($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not add unit.');
        }
        return redirect()->back()->with('success', 'Unit added successfully!');
    }
} 