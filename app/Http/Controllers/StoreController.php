<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $businesses = $user->ownedBusinesses()->with('branches.stores')->get();
        $branches = $businesses->flatMap->branches;
        $stores = $branches->flatMap(function($br) {
            return $br->stores->isEmpty() ? [$br->effectiveStore()] : $br->stores;
        });
        return Inertia::render('Stores/Index', [
            'stores' => $stores->values(),
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $businesses = $user->ownedBusinesses()->get();
        $branches = $businesses->flatMap->branches;
        return Inertia::render('Stores/Create', [
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|boolean',
        ]);
        if (!$user->ownedBusinesses()->where('id', $validated['business_id'])->exists()) {
            abort(403);
        }
        Store::create($validated);
        return redirect()->route('stores.index')->with('success', 'Store created.');
    }

    public function edit(Store $store)
    {
        $user = auth()->user();
        $this->authorize('view', $store);
        $businesses = $user->ownedBusinesses()->get();
        $branches = $businesses->flatMap->branches;
        return Inertia::render('Stores/Edit', [
            'store' => $store,
            'businesses' => $businesses,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, Store $store)
    {
        $user = auth()->user();
        $this->authorize('update', $store);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|boolean',
        ]);
        $store->update($validated);
        return redirect()->route('stores.index')->with('success', 'Store updated.');
    }

    public function destroy(Store $store)
    {
        $user = auth()->user();
        $this->authorize('delete', $store);
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Store deleted.');
    }
} 