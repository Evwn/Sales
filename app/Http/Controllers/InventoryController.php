<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Inventory/Index', [
            'inventory' => Inventory::with(['branch.business', 'product'])->paginate(10),
        ]);
    }

    public function create()
    {
        $products = Product::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('admin_id', auth()->id());
                });
        })->get();

        $branches = Branch::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('admin_id', auth()->id());
                });
        })->get();

        return Inertia::render('Inventory/Create', [
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:0',
            'threshold' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->authorize('update', $product->business);

        $inventory = Inventory::create($validated);

        return redirect()->route('inventory.index')
            ->with('message', 'Inventory created successfully.');
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory->product->business);

        $inventory->load(['product.business', 'branch']);

        return Inertia::render('Inventory/Show', [
            'inventory' => $inventory,
        ]);
    }

    public function edit(Inventory $inventory)
    {
        $this->authorize('update', $inventory->product->business);

        $products = Product::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('admin_id', auth()->id());
                });
        })->get();

        $branches = Branch::whereHas('business', function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('admins', function ($q) {
                    $q->where('admin_id', auth()->id());
                });
        })->get();

        return Inertia::render('Inventory/Edit', [
            'inventory' => $inventory,
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('update', $inventory->product->business);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:0',
            'threshold' => 'required|integer|min:0',
        ]);

        $inventory->update($validated);

        return redirect()->route('inventory.index')
            ->with('message', 'Inventory updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('delete', $inventory->product->business);

        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('message', 'Inventory deleted successfully.');
    }
} 