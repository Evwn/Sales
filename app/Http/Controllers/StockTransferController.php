<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $businesses = $user->ownedBusinesses()->with('branches')->get();
        $branches = $businesses->flatMap->branches;
        $locations = Location::whereHas('locationType', function ($q) {
            $q->where(function ($q2) {
                $q2->where('user_id', auth()->id())
                ->orWhereNull('user_id');
            })->where('name', 'store');
        })->get();

        $transfers = StockTransfer::whereIn('from_location_id', $locations->pluck('id'))
            ->orWhereIn('to_location_id', $locations->pluck('id'))
            ->with(['fromStore', 'toStore'])
            ->get();
        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers,
            'businesses' => $businesses,
            'branches' => $branches,
            'locations' => $locations,
        ]);
    }

    public function create()
    {   $user = auth()->user();
        $locations = Location::whereIn('business_id', auth()->user()->ownedBusinesses()->pluck('id'))->get();
        
        $items = \App\Models\Item::with('variants')->get();
        $itemData = collect();
        $itemIds = [];
        $variantIds = [];
        foreach ($items as $item) {
            if ($item->variants->count()) {
                foreach ($item->variants as $variant) {
                    $itemData->push([
                        'id' => $variant->id,
                        'item_id' => $item->id,
                        'variant_id' => $variant->id,
                        'name' => $item->name,
                        'optionsString' => $variant->options_string ?? '',
                        'sku' => $variant->sku,
                        'in_stock' => $variant->in_stock ?? 0,
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                    ]);
                    $variantIds[] = $variant->id;
                }
            }
            $itemData->push([
                'id' => $item->id,
                'item_id' => $item->id,
                'variant_id' => null,
                'name' => $item->name,
                'optionsString' => '',
                'sku' => $item->sku,
                'in_stock' => $item->in_stock ?? 0,
                'is_variant' => false,
                'parent_item_id' => null,
            ]);
            $itemIds[] = $item->id;
        }
        $locationIds = $locations->pluck('id')->all();
        $stockItems = \App\Models\StockItem::whereIn('location_id', $locationIds)
            ->where(function($q) use ($itemIds, $variantIds) {
                $q->whereIn('item_id', $itemIds);
                if (!empty($variantIds)) {
                    $q->orWhereIn('variant_id', $variantIds);
                }
            })->get();
        $stockMap = [];
        foreach ($stockItems as $stock) {
            $key = $stock->location_id . '_' . $stock->item_id . '_' . ($stock->variant_id ?? '0');
            $stockMap[$key] = $stock->quantity;
        }
        return Inertia::render('StockTransfers/Create', [
            'locations' => $locations,
            'items' => $itemData,
            'stockMap' => $stockMap,
        ]);
    }

    public function store(Request $request)
    { 
        $validated = $request->validate([
            'from_store_id' => 'required|exists:locations,id',
            'to_store_id' => 'required|exists:locations,id|different:from_store_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);
        $reference=StockTransfer::generateReference();

        
    DB::beginTransaction();

    try {
        $transfer = StockTransfer::create([
            'from_location_id' => $validated['from_store_id'],
            'to_location_id' => $validated['to_store_id'],
            'reference' => $reference,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);
        foreach ($validated['items'] as $item) {
            $transfer->items()->create([
                'stock_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
            ]);
             $stockItem = StockItem::where('location_id', $validated['from_store_id'])
            ->where('item_id', $item['item_id'])
            ->first();

        if (!$stockItem) {
            throw new \Exception("Item ID {$item['item_id']} not found in the source location.");
        }

        if ($stockItem->quantity < $item['quantity']) {
            throw new \Exception("Not enough stock for Item ID {$item['item_id']} at source location.");
        }

        $stockItem->decrement('quantity', $item['quantity']);
        }
            DB::commit();
        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer created.');
            } catch (\Exception $e) {
        DB::rollBack();


         return back()->withErrors(['error' => 'Failed to create stock transfer: ' . $e->getMessage()]);
    }
    }
    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $allReceived = true;

            foreach ($validated['items'] as $itemData) {

                // Always fetch the latest data from DB
                $transferItem = $stockTransfer->items()->where('id', $itemData['id'])->firstOrFail();
                $transferItem->refresh();

                $requestedQty = (float) $itemData['received_quantity'];
                $remainingQty = (float) $transferItem->quantity - (float) $transferItem->quantity_received;

                $qtyToAdd = min($requestedQty, $remainingQty);
                if ($qtyToAdd <= 0) {
                    continue;
                }

                // Update stock_transfer_items (increment quantity_received)
                $transferItem->quantity_received += $qtyToAdd;
                $transferItem->save();

                if ($transferItem->quantity_received < $transferItem->quantity) {
                    $allReceived = false;
                }

                $itemId = $transferItem->stockItem->item_id;
                $variantId = $transferItem->stockItem->variant_id;

                // Update destination stock
                $toStock = \App\Models\StockItem::where([
                    'location_id' => $stockTransfer->to_location_id,
                    'item_id'     => $itemId,
                    'variant_id'  => $variantId,
                ])->first();

                if ($toStock) {
                    $toStock->increment('quantity', $qtyToAdd);
                } else {
                    \App\Models\StockItem::create([
                        'location_id'     => $stockTransfer->to_location_id,
                        'item_id'         => $itemId,
                        'variant_id'      => $variantId,
                        'quantity'        => $qtyToAdd,
                        'min_stock_level' => 0,
                        'max_stock_level' => 0,
                    ]);
                }
            }

            $stockTransfer->update([
                'status' => $allReceived ? 'completed' : 'partially_received'
            ]);

            DB::commit();

            return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer received.');

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Stock receive error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to receive stock transfer. Please try again.');
        }
    }



    public function receiveForm(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['items.product.item','items.product', 'fromStore', 'toStore']);

        $filteredItems = $stockTransfer->items->filter(function ($item) {
            return ($item->quantity - $item->quantity_received) > 0;
        })->map(function ($item) {
            $remaining = $item->quantity - $item->quantity_received;
            $item->quantity = $remaining;
            return $item;
        })->values();

        $stockTransfer->setRelation('items', $filteredItems);

        return Inertia::render('StockTransfers/Receive', [
            'transfer' => $stockTransfer,
        ]);
    }


}
