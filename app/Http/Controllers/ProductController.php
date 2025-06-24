<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Customer;
use App\Services\ActivityLogger;

class ProductController extends Controller
{
    public function all()
    {
        $user = Auth::user();
        $query = Product::query();

        // If user is a seller, only show products from their branch
        if ($user->hasRole('seller')) {
            $query->where('branch_id', $user->branch_id);
        }
        // If user is an owner, only show products from branches of their businesses
        elseif ($user->hasRole('owner')) {
            $query->whereHas('branch.business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('admins', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }
        // If user is an admin, only show products from businesses they manage
        elseif ($user->hasRole('admin')) {
            $query->whereHas('branch.business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('admins', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        $products = $query->with(['inventoryItem', 'branch.business'])
            ->paginate(10)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->inventoryItem->name,
                    'description' => $product->inventoryItem->description,
                    'price' => $product->price,
                    'buying_price' => $product->buying_price,
                    'barcode' => $product->inventoryItem->barcode,
                    'sku' => $product->inventoryItem->sku,
                    'stock' => $product->stock,
                    'min_stock_level' => $product->min_stock_level,
                    'tax_rate' => $product->inventoryItem->tax_rate,
                    'is_taxable' => $product->inventoryItem->is_taxable,
                    'branch' => $product->branch ? [
                        'id' => $product->branch->id,
                        'name' => $product->branch->name
                    ] : null,
                    'business' => $product->branch ? [
                        'id' => $product->branch->business->id,
                        'name' => $product->branch->business->name
                    ] : null,
                    'inventory_item' => [
                        'image_url' => $product->inventoryItem->image_url ?? null
                    ],
                    'image_url' => $product->inventoryItem->image_url ?? null
                ];
            });

        // Get businesses for the current user
        $businesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get(['id', 'name']);

        $defaultCustomer = Customer::where('email', 'walkin@default.com')->first();

        return Inertia::render('Products/All', [
            'products' => $products,
            'businesses' => $businesses,
            'defaultCustomerId' => $defaultCustomer ? $defaultCustomer->id : null,
            'sales' => [], // Always provide sales, even if empty
        ]);
    }

    public function index(Branch $branch)
    {
        $products = $branch->products()
            ->with('inventoryItem')
            ->paginate(10)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->inventoryItem->name,
                    'description' => $product->inventoryItem->description,
                    'price' => $product->price,
                    'barcode' => $product->inventoryItem->barcode,
                    'stock' => $product->stock,
                    'min_stock_level' => $product->min_stock_level,
                    'tax_rate' => $product->inventoryItem->tax_rate,
                    'is_taxable' => $product->inventoryItem->is_taxable,
                    'branch' => $product->branch ? [
                        'id' => $product->branch->id,
                        'name' => $product->branch->name
                    ] : null
                ];
            });

        return Inertia::render('Products/Index', [
            'branch' => $branch,
            'products' => $products
        ]);
    }

    public function create(Branch $branch)
    {
        return Inertia::render('Products/Create', [
            'branch' => $branch,
        ]);
    }

    public function store(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        // Check if product already exists in this branch
        $existingProduct = Product::where('inventory_item_id', $validated['inventory_item_id'])
            ->where('branch_id', $branch->id)
            ->first();

        if ($existingProduct) {
            return back()->withErrors([
                'message' => 'This product is already added to this branch.'
            ]);
        }

        // Create the product
        $product = Product::create([
            'inventory_item_id' => $validated['inventory_item_id'],
            'price' => $validated['price'],
            'buying_price' => $validated['buying_price'],
            'stock' => $validated['stock'],
            'min_stock_level' => $validated['min_stock_level'],
            'branch_id' => $branch->id,
            'status' => 1
        ]);

        return back()->with('success', 'Product added successfully.');
    }

    public function show(Branch $branch, Product $product)
    {
        // Check if product belongs to this branch
        if ($product->branch_id !== $branch->id) {
            abort(404);
        }

        $product->load(['branch', 'inventoryItem']);

        return Inertia::render('Products/Show', [
            'branch' => $branch,
            'product' => $product,
        ]);
    }

    public function edit(Branch $branch, Product $product)
    {
        // Check if product belongs to this branch
        if ($product->branch_id !== $branch->id) {
            abort(404);
        }

        $product->load(['branch', 'inventoryItem']);

        return Inertia::render('Products/Edit', [
            'branch' => $branch,
            'product' => $product,
        ]);
    }

    public function update(Request $request, Branch $branch, Product $product)
    {
        // Check if product belongs to this branch
        if ($product->branch_id !== $branch->id) {
            abort(404);
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ]);

        $oldStock = $product->stock;
        $product->update($validated);

        // Log the activity
        ActivityLogger::logProductUpdated($product, auth()->user());

        // If stock was adjusted, log that separately
        if ($oldStock !== $product->stock) {
            ActivityLogger::logInventoryAdjusted($product, auth()->user(), $oldStock, $product->stock);
        }

        return redirect()->route('products.branch.index', $branch)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Branch $branch, Product $product)
    {
        // Check if product belongs to this branch
        if ($product->branch_id !== $branch->id) {
            abort(404);
        }

        $product->delete();

        return back()->with('success', 'Product removed successfully.');
    }
} 