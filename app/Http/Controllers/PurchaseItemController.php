<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseItemController extends Controller
{
    public function index()
    {
        $items = PurchaseItem::all();
        return Inertia::render('PurchaseItems/Index', ['items' => $items]);
    }

    public function create()
    {
        return Inertia::render('PurchaseItems/Create');
    }

    public function store(Request $request)
    {
        $item = PurchaseItem::create($request->all());
        return redirect()->route('purchase-items.index');
    }

    public function show(PurchaseItem $purchaseItem)
    {
        return Inertia::render('PurchaseItems/Show', ['purchaseItem' => $purchaseItem]);
    }

    public function edit(PurchaseItem $purchaseItem)
    {
        return Inertia::render('PurchaseItems/Edit', ['purchaseItem' => $purchaseItem]);
    }

    public function update(Request $request, PurchaseItem $purchaseItem)
    {
        $purchaseItem->update($request->all());
        return redirect()->route('purchase-items.index');
    }

    public function destroy(PurchaseItem $purchaseItem)
    {
        $purchaseItem->delete();
        return redirect()->route('purchase-items.index');
    }
} 