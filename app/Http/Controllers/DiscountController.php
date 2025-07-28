<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::all();
        return Inertia::render('Discounts/Index', ['discounts' => $discounts]);
    }

    public function create()
    {
        return Inertia::render('Discounts/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'business_id' => 'required|exists:businesses,id',
        ]);
        Discount::create($data);
        return redirect()->route('discounts.index');
    }

    public function edit(Discount $discount)
    {
        return Inertia::render('Discounts/Edit', ['discount' => $discount]);
    }

    public function update(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'business_id' => 'required|exists:businesses,id',
        ]);
        $discount->update($data);
        return redirect()->route('discounts.index');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index');
    }
} 