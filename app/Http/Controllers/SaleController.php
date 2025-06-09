<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Business $business, Branch $branch)
    {
        $query = Sale::where('branch_id', $branch->id);

        // If seller, only show their own sales
        if (Auth::user()->role === 'seller') {
            $query->where('seller_id', Auth::id());
        }

        $sales = $query->with(['seller', 'products', 'branch.business'])->get();

        return view('sales.index', compact('business', 'branch', 'sales'));
    }

    public function create(Business $business, Branch $branch)
    {
        return view('sales.create', compact('business', 'branch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card',
            'branch_id' => 'required|exists:branches,id',
            'business_id' => 'required|exists:businesses,id',
        ]);

        // Verify seller has access to this branch
        if (Auth::user()->role === 'seller' && Auth::user()->branch_id !== $validated['branch_id']) {
            return response()->json(['message' => 'Unauthorized access to branch'], 403);
        }

        // Get the product
        $product = Product::findOrFail($validated['product_id']);

        // Verify stock
        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Insufficient stock'], 422);
        }

        // Create the sale
        $sale = Sale::create([
            'seller_id' => Auth::id(),
            'branch_id' => $validated['branch_id'],
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'],
        ]);

        // Attach the product to the sale
        $sale->products()->attach($product->id, [
            'quantity' => $validated['quantity'],
            'price' => $product->price,
        ]);

        // Update product stock
        $product->decrement('stock', $validated['quantity']);

        return response()->json(['message' => 'Sale created successfully']);
    }

    public function show(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // If seller, verify they own the sale
        if (Auth::user()->role === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->load(['seller', 'products']);

        return view('sales.show', compact('business', 'branch', 'sale'));
    }

    public function edit(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow editing recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be edited within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->load(['products']);

        return view('sales.edit', compact('business', 'branch', 'sale'));
    }

    public function update(Request $request, Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow editing recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be edited within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card',
            'notes' => 'nullable|string',
        ]);

        $sale->update([
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Sync products
        $sale->products()->sync(collect($validated['products'])->mapWithKeys(function ($product) {
            return [$product['id'] => [
                'quantity' => $product['quantity'],
                'price' => $product['price'],
            ]];
        }));

        return redirect()->route('sales.show', [$business, $branch, $sale])
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Business $business, Branch $branch, Sale $sale)
    {
        if ($sale->branch_id !== $branch->id) {
            abort(404);
        }

        // Only allow deleting recent sales (within 24 hours)
        if ($sale->created_at->diffInHours(now()) > 24) {
            abort(403, 'Sales can only be deleted within 24 hours of creation.');
        }

        // If seller, verify they own the sale
        if (Auth::user()->role === 'seller' && $sale->seller_id !== Auth::id()) {
            abort(403);
        }

        $sale->products()->detach();
        $sale->delete();

        return redirect()->route('sales.index', [$business, $branch])
            ->with('success', 'Sale deleted successfully.');
    }

    public function all()
    {
        $user = Auth::user();
        $query = Sale::query();

        // If user is a seller, only show their branch's sales
        if ($user->role === 'seller') {
            $query->where('branch_id', $user->branch_id);
        } else {
            // For admins, only show sales from their business's branches
            $query->whereHas('branch.business', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('admins', function ($query) use ($user) {
                        $query->where('admin_id', $user->id);
                    });
            });
        }

        return Inertia::render('Sales/Index', [
            'sales' => $query->with(['branch.business', 'seller', 'products'])->paginate(10),
        ]);
    }
} 