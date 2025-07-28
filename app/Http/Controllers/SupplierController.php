<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('business_id', auth()->user()->business_id)->get();
        return Inertia::render('Suppliers/Index', ['suppliers' => $suppliers]);
    }

    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'status' => 'required|boolean',
            'bank_details' => 'nullable|string',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:255',
        ]);
        $validated['business_id'] = auth()->user()->business_id;
        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'status' => 'required|boolean',
            'bank_details' => 'nullable|string',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:255',
        ]);
        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function items($supplierId)
    {
        // Fetch items for this supplier (customize as needed)
        $items = \App\Models\Item::with('variants')
            ->where('supplier_id', $supplierId)
            ->get();

        $result = collect();
        foreach ($items as $item) {
            if ($item->variants->count()) {
                foreach ($item->variants as $variant) {
                    $result->push([
                        'id' => $variant->id,
                        'name' => $item->name,
                        'optionsString' => $variant->options_string ?? '',
                        'sku' => $variant->sku,
                        'in_stock' => $variant->in_stock ?? 0,
                        'low_stock' => $variant->low_stock ?? 0,
                        'cost' => $variant->cost,
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                    ]);
                }
            } else {
                $result->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'in_stock' => $item->in_stock ?? 0,
                    'low_stock' => $item->low_stock ?? 0,
                    'cost' => $item->cost,
                    'is_variant' => false,
                    'parent_item_id' => null,
                ]);
            }
        }
        return response()->json($result);
    }
} 