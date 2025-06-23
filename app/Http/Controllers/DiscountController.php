<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscountController extends Controller
{
    public function index()
    {
        return Inertia::render('Discounts/Index', [
            'discounts' => Discount::with(['business'])->paginate(10),
        ]);
    }

    public function create()
    {
        $businesses = Business::where('owner_id', auth()->id())
            ->orWhereHas('admins', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        $products = Product::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('user_id', auth()->id());
                });
        })->get();

        return Inertia::render('Discounts/Create', [
            'businesses' => $businesses,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'product_id' => 'nullable|exists:products,id',
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $this->authorize('update', $business);

        if ($validated['product_id']) {
            $product = Product::findOrFail($validated['product_id']);
            if ($product->business_id !== $business->id) {
                return back()->withErrors(['product_id' => 'The selected product does not belong to the selected business.']);
            }
        }

        $discount = Discount::create($validated);

        return redirect()->route('discounts.index')
            ->with('message', 'Discount created successfully.');
    }

    public function show(Discount $discount)
    {
        $this->authorize('view', $discount->business);

        $discount->load(['business', 'product']);

        return Inertia::render('Discounts/Show', [
            'discount' => $discount,
        ]);
    }

    public function edit(Discount $discount)
    {
        $this->authorize('update', $discount->business);

        $businesses = Business::where('owner_id', auth()->id())
            ->orWhereHas('admins', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        $products = Product::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('user_id', auth()->id());
                });
        })->get();

        return Inertia::render('Discounts/Edit', [
            'discount' => $discount,
            'businesses' => $businesses,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Discount $discount)
    {
        $this->authorize('update', $discount->business);

        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'product_id' => 'nullable|exists:products,id',
            'type' => 'required|in:percentage,flat',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);

        if ($validated['product_id']) {
            $product = Product::findOrFail($validated['product_id']);
            if ($product->business_id !== $validated['business_id']) {
                return back()->withErrors(['product_id' => 'The selected product does not belong to the selected business.']);
            }
        }

        $discount->update($validated);

        return redirect()->route('discounts.index')
            ->with('message', 'Discount updated successfully.');
    }

    public function destroy(Discount $discount)
    {
        $this->authorize('delete', $discount->business);

        $discount->delete();

        return redirect()->route('discounts.index')
            ->with('message', 'Discount deleted successfully.');
    }
} 