<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;

class ProductController extends Controller
{
    public function all()
    {
        $user = Auth::user();
        
        // Get all businesses the user has access to
        $businesses = $user->businesses()->get();
        
        if ($businesses->isEmpty()) {
            return Inertia::render('Products/NoBusiness', [
                'message' => 'You do not have access to any businesses. Please contact your administrator.'
            ]);
        }

        // Get all products from accessible businesses, filtered by user's branch if they are a seller
        $query = Product::whereIn('business_id', $businesses->pluck('id'));
        
        // If user is a seller, only show products from their branch
        if ($user->isSeller()) {
            $query->where('branch_id', $user->branch_id);
        }

        $products = $query->with(['inventoryItem', 'business', 'branch'])
            ->paginate(10)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->inventoryItem->name,
                    'description' => $product->inventoryItem->description,
                    'price' => $product->price,
                    'barcode' => $product->inventoryItem->barcode,
                    'sku' => $product->inventoryItem->sku,
                    'stock' => $product->stock,
                    'business' => [
                        'id' => $product->business->id,
                        'name' => $product->business->name
                    ],
                    'branch' => $product->branch ? [
                        'id' => $product->branch->id,
                        'name' => $product->branch->name
                    ] : null
                ];
            });

        return Inertia::render('Products/All', [
            'businesses' => $businesses,
            'products' => $products
        ]);
    }

    public function index(Business $business)
    {
        $products = $business->products()
            ->with('inventoryItem')
            ->paginate(10)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->inventoryItem->name,
                    'description' => $product->inventoryItem->description,
                    'price' => $product->price,
                    'barcode' => $product->inventoryItem->barcode,
                    'sku' => $product->inventoryItem->sku,
                    'stock' => $product->stock,
                    'branch' => $product->branch ? [
                        'id' => $product->branch->id,
                        'name' => $product->branch->name
                    ] : null
                ];
            });

        return Inertia::render('Products/Index', [
            'business' => $business,
            'products' => $products
        ]);
    }

    public function create(Branch $branch)
    {
        return Inertia::render('Products/Create', [
            'branch' => $branch,
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Check if product already exists
        $existingProduct = $business->products()
            ->where('inventory_item_id', $validated['inventory_item_id'])
            ->first();

        if ($existingProduct) {
            return back()->withErrors([
                'message' => 'This product is already added to your business.'
            ]);
        }

        // Get the inventory item
        $inventoryItem = InventoryItem::findOrFail($validated['inventory_item_id']);

        // Create the product
        $product = Product::create([
            'inventory_item_id' => $validated['inventory_item_id'],
            'business_id' => $business->id,
            'price' => $validated['price'],
            'buying_price' => $validated['buying_price'],
            'stock' => $validated['stock'],
            'branch_id' => $validated['branch_id'],
            'status' => 'active'
        ]);

        return back()->with('success', 'Product added successfully.');
    }

    public function show(Business $business, Product $product)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        // Check if product belongs to this business
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $product->load(['branch', 'inventory', 'inventoryItem']);

        return Inertia::render('Products/Show', [
            'business' => $business,
            'product' => $product,
        ]);
    }

    public function edit(Business $business, Product $product)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        // Check if product belongs to this business
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $product->load(['branch', 'inventoryItem']);

        return Inertia::render('Products/Edit', [
            'business' => $business,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Business $business, Product $product)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        // Check if product belongs to this business
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $product->update($validated);

        return redirect()->route('products.index', $business)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Business $business, Product $product)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        // Check if product belongs to this business
        if ($product->business_id !== $business->id) {
            abort(404);
        }

        $product->delete();

        return back()->with('success', 'Product removed successfully.');
    }
} 