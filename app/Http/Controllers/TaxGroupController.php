<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaxGroupController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $validated = $request->validate([
            'code' => 'required|string|max:2',
            'description' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
        ]);
        $taxGroup = \App\Models\TaxGroup::create([
            'code' => $validated['code'],
            'description' => $validated['description'],
            'rate' => $validated['rate'],
            'user_id' => auth()->id(),
        ]);
        return response()->json($taxGroup);
    }
}