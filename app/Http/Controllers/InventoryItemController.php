<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query()
            ->with(['lastUpdatedBy'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('upc', 'like', "%{$search}%");
                });
            })
            ->when($request->input('brand'), function ($query, $brand) {
                $query->where('brand', $brand);
            });

        $items = $query->paginate(10)->withQueryString();

        // Get unique brands for filter
        $brands = InventoryItem::distinct()->pluck('brand')->filter()->values();

        // Get businesses for the current user
        $businesses = \App\Models\Business::where('owner_id', Auth::id())
            ->orWhereHas('admins', function ($query) {
                $query->where('admin_id', Auth::id());
            })
            ->get(['id', 'name', 'description']);

        // Get user with managed branches and their business relationship
        $user = Auth::user();
        $managedBranches = \App\Models\Branch::whereHas('business', function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhereHas('admins', function ($q) use ($user) {
                    $q->where('admin_id', $user->id);
                });
        })->with('business')->get();

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'managedBranches' => $managedBranches
        ];

        return Inertia::render('InventoryItems/Index', [
            'items' => $items,
            'brands' => $brands,
            'filters' => $request->only(['search', 'brand']),
            'businesses' => $businesses,
            'auth' => [
                'user' => $userData
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryItems/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items',
            'barcode' => 'nullable|string|max:255|unique:inventory_items',
            'upc' => 'nullable|string|max:255|unique:inventory_items',
            'ean' => 'nullable|string|max:255|unique:inventory_items',
            'isbn' => 'nullable|string|max:255|unique:inventory_items',
            'mpn' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
        ]);

        $item = InventoryItem::create([
            ...$validated,
            'created_by' => Auth::id(),
            'last_updated_by' => Auth::id(),
        ]);

        return redirect()->route('inventory-items.show', $item)
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(InventoryItem $inventoryItem)
    {
        $inventoryItem->load(['creator', 'lastUpdatedBy', 'products.business']);
        return Inertia::render('InventoryItems/Show', [
            'inventoryItem' => $inventoryItem
        ]);
    }

    public function edit(InventoryItem $inventoryItem)
    {
        return Inertia::render('InventoryItems/Edit', [
            'inventoryItem' => $inventoryItem
        ]);
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:inventory_items,sku,' . $inventoryItem->id,
            'barcode' => 'nullable|string|max:255|unique:inventory_items,barcode,' . $inventoryItem->id,
            'upc' => 'nullable|string|max:255|unique:inventory_items,upc,' . $inventoryItem->id,
            'ean' => 'nullable|string|max:255|unique:inventory_items,ean,' . $inventoryItem->id,
            'isbn' => 'nullable|string|max:255|unique:inventory_items,isbn,' . $inventoryItem->id,
            'mpn' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
        ]);

        $inventoryItem->update([
            ...$validated,
            'last_updated_by' => Auth::id(),
        ]);

        return redirect()->route('inventory-items.show', $inventoryItem)
            ->with('success', 'Inventory item updated successfully.');
    }

    // Note: No destroy method as we don't want to allow deletion

    public function search(Request $request)
    {
        $query = $request->input('query', '');
        
        $items = InventoryItem::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('sku', 'like', "%{$query}%")
              ->orWhere('barcode', 'like', "%{$query}%");
        })
        ->select(['id', 'name', 'description', 'sku', 'barcode', 'unit', 'unit_value'])
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'unit_display' => $item->unit_display,
            ];
        });

        return response()->json($items);
    }
} 