<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Location;
use App\Models\Product;
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
        $locations = Location::whereHas('locationType', function($q) { $q->where('name', 'store'); })->get();
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
        $locations = Location::with('locationType')->whereHas('locationType', function($q) { $q->where('name', 'store'); })->get();
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
    {    \Log::info('Stock Transfer', [
            'Store from' => $request->from_store_id,
            'Store to' => $request->to_store_id,
            'notes' => $request->notes,
            'items' => $request->items,
        ]);
        $validated = $request->validate([
            'from_store_id' => 'required|exists:locations,id',
            'to_store_id' => 'required|exists:locations,id|different:from_store_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);
        \Log::info('Stock Transfer', [
            'Validation' => $validated,
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
         \Log::info('Stock Transfer', [
            'Reference' => $reference,
        ]);
        foreach ($validated['items'] as $item) {
            $transfer->items()->create([
                'stock_item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
            ]);
        }
            DB::commit();
        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer created.');
            } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Failed to create stock transfer', [
            'error' => $e->getMessage(),
        ]);

         return back()->withErrors(['error' => 'Failed to create stock transfer: ' . $e->getMessage()]);
    }
    }


    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        $user = auth()->user();
        // Add a policy for receive if needed
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);
        foreach ($validated['items'] as $item) {
            $transferItem = $stockTransfer->items()->find($item['id']);
            $transferItem->update(['received_quantity' => $item['received_quantity']]);
            // Update stock logic here...
        }
        $stockTransfer->status = 'completed';
        $stockTransfer->save();
        return redirect()->route('stock-transfers.index')->with('success', 'Stock transfer received.');
    }
} 