<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseItemController extends Controller
{
    public function index()
    {   
        $user = auth()->user();

         $items = PurchaseItem::with(['purchase', 'item', 'stockItem.location'])
            ->whereHas('item', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        foreach ($items as $item) {
            \Log::info('Purchase items', [
                'logged in user id'   => $user->id,
                'User id'             => $item->item?->user_id,
                'Item_id'             => $item->id,
                'purchase_ref'        => $item->purchase?->reference,
                'Item name'           => $item->item?->name,
                'Location name'       => $item->stockItem?->location?->name,
                'quantity_ordered'    => $item->quantity_ordered,
                'quantity_received'   => $item->quantity_received,
                'purchase_cost'       => $item->purchase_cost,
                'Additional_cost'     => $item->proportional_additional_cost,
                'status'              => $item->status,
            ]);
        }
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