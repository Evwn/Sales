<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Supplier;
use App\Models\Store;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'location.locationType', 'items']);
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $purchases = $query->orderByDesc('id')->get()->map(function ($purchase) {
            $total_ordered = $purchase->items->sum('quantity_ordered');
            $total_received = $purchase->items->sum('quantity_received');
            return array_merge($purchase->toArray(), [
                'total_ordered' => $total_ordered,
                'total_received' => $total_received,
            ]);
        });
        return Inertia::render('Purchases/Index', ['purchases' => $purchases]);
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $stores = \App\Models\Location::with('locationType')
            ->where('user_id', auth()->id())
            ->get();
        // Fetch all non-composite items and variants
        $items = \App\Models\Item::with('variants')->where('is_composite', false)->get();
        $itemData = collect();
        
        foreach ($items as $item) {
            if ($item->variants->count()) {
                foreach ($item->variants as $variant) {
                    $itemData->push([
                        'id' => $variant->id,
                        'name' => $item->name,
                        'optionsString' => $variant->options_string ?? '',
                        'sku' => $variant->sku,
                        'in_stock' => 0, // Will be calculated per location
                        'incoming' => 0, // Will be calculated per location
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                    ]);
                }
            } else {
                $itemData->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'in_stock' => 0, // Will be calculated per location
                    'incoming' => 0, // Will be calculated per location
                    'is_variant' => false,
                    'parent_item_id' => null,
                ]);
            }
        }
        return Inertia::render('Purchases/Create', [
            'suppliers' => $suppliers,
            'stores' => $stores,
            'items' => $itemData,
        ]);
    }

    public function getLocationStock(Request $request)
    {
        $locationId = $request->input('location_id');
        
        if (!$locationId) {
            return response()->json(['error' => 'Location ID is required'], 400);
        }
        
        // Get all non-composite items and variants
        $items = \App\Models\Item::with('variants')->where('is_composite', false)->get();
        $itemData = collect();
        
        foreach ($items as $item) {
            if ($item->variants->count()) {
                foreach ($item->variants as $variant) {
                    // Get stock for this variant at the specific location
                    $stockQuantity = \App\Models\StockItem::where('location_id', $locationId)
                        ->where('item_id', $item->id)
                        ->where('variant_id', $variant->id)
                        ->value('quantity') ?? 0;
                    
                    // Get max stock level for this variant at the specific location
                    $maxStockLevel = \App\Models\StockItem::where('location_id', $locationId)
                        ->where('item_id', $item->id)
                        ->where('variant_id', $variant->id)
                        ->value('max_stock_level');
                    
                    // Calculate incoming for this variant at the specific location
                    $incoming = \App\Models\PurchaseItem::where('variant_id', $variant->id)
                        ->whereHas('purchase', function($query) use ($locationId) {
                            $query->where('location_id', $locationId);
                        })
                        ->whereIn('status', ['pending', 'partially_received'])
                        ->sum(DB::raw('quantity_ordered - quantity_received'));
                    
                    // Get most recent purchase price for this variant
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $variant->id)
                        ->whereNotNull('unit_price')
                        ->where('unit_price', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('unit_price') ?? 0;
                    
                    // If no unit_price found, try purchase_cost
                    if ($recentPurchasePrice == 0) {
                        $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $variant->id)
                            ->whereNotNull('purchase_cost')
                            ->where('purchase_cost', '>', 0)
                            ->orderBy('created_at', 'desc')
                            ->value('purchase_cost') ?? 0;
                    }
                    
                    $itemData->push([
                        'id' => $variant->id,
                        'name' => $item->name,
                        'optionsString' => $variant->options_string ?? '',
                        'sku' => $variant->sku,
                        'in_stock' => (float)$stockQuantity,
                        'incoming' => (float)$incoming,
                        'max_stock_level' => $maxStockLevel ? (float)$maxStockLevel : null,
                        'recent_purchase_price' => (float)$recentPurchasePrice,
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                    ]);
                }
            } else {
                // Get stock for this item at the specific location
                $stockQuantity = \App\Models\StockItem::where('location_id', $locationId)
                    ->where('item_id', $item->id)
                    ->whereNull('variant_id')
                    ->value('quantity') ?? 0;
                
                // Get max stock level for this item at the specific location
                $maxStockLevel = \App\Models\StockItem::where('location_id', $locationId)
                    ->where('item_id', $item->id)
                    ->whereNull('variant_id')
                    ->value('max_stock_level');
                
                // Calculate incoming for this item at the specific location
                $incoming = \App\Models\PurchaseItem::where('item_id', $item->id)
                    ->whereNull('variant_id')
                    ->whereHas('purchase', function($query) use ($locationId) {
                        $query->where('location_id', $locationId);
                    })
                    ->whereIn('status', ['pending', 'partially_received'])
                    ->sum(DB::raw('quantity_ordered - quantity_received'));
                
                // Get most recent purchase price for this item
                $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $item->id)
                    ->whereNull('variant_id')
                    ->whereNotNull('unit_price')
                    ->where('unit_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('unit_price') ?? 0;
                
                // If no unit_price found, try purchase_cost
                if ($recentPurchasePrice == 0) {
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $item->id)
                        ->whereNotNull('purchase_cost')
                        ->where('purchase_cost', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('purchase_cost') ?? 0;
                }
                
                $itemData->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'in_stock' => (float)$stockQuantity,
                    'incoming' => (float)$incoming,
                    'max_stock_level' => $maxStockLevel ? (float)$maxStockLevel : null,
                    'recent_purchase_price' => (float)$recentPurchasePrice,
                    'is_variant' => false,
                    'parent_item_id' => null,
                ]);
            }
        }
        
        return response()->json($itemData);
    }

    public function getSupplierItems(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        $locationId = $request->input('location_id');
        
        if (!$supplierId) {
            return response()->json(['error' => 'Supplier ID is required'], 400);
        }
        
        if (!$locationId) {
            return response()->json(['error' => 'Location ID is required'], 400);
        }
        
        // Get ALL purchase items from this supplier at this location (not just unique), excluding composite items
        $purchasedItems = \App\Models\PurchaseItem::whereHas('purchase', function($query) use ($supplierId, $locationId) {
            $query->where('supplier_id', $supplierId)
                  ->where('location_id', $locationId);
        })
        ->whereHas('item', function($query) {
            $query->where('is_composite', false);
        })
        ->with(['item', 'variant'])
        ->get();
        
        $itemData = collect();
        $processedItems = collect(); // Track processed items to avoid duplicates
        
        foreach ($purchasedItems as $purchaseItem) {
            // Create unique key for each item/variant combination (item_id + variant_id pair)
            $itemKey = $purchaseItem->item_id . '_' . ($purchaseItem->variant_id ?? 'null');
            
            // Skip if we've already processed this item/variant combination
            if ($processedItems->contains($itemKey)) {
                continue;
            }
            
            $processedItems->push($itemKey);
            
            if ($purchaseItem->variant) {
                // Get most recent purchase price for this variant from this supplier at this location
                $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $purchaseItem->variant->id)
                    ->whereHas('purchase', function($query) use ($supplierId, $locationId) {
                        $query->where('supplier_id', $supplierId)
                              ->where('location_id', $locationId);
                    })
                    ->whereNotNull('unit_price')
                    ->where('unit_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('unit_price') ?? 0;
                
                if ($recentPurchasePrice == 0) {
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $purchaseItem->variant->id)
                        ->whereHas('purchase', function($query) use ($supplierId, $locationId) {
                            $query->where('supplier_id', $supplierId)
                                  ->where('location_id', $locationId);
                        })
                        ->whereNotNull('purchase_cost')
                        ->where('purchase_cost', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('purchase_cost') ?? 0;
                }
                
                $itemData->push([
                    'id' => $purchaseItem->variant->id,
                    'name' => $purchaseItem->item->name,
                    'optionsString' => $purchaseItem->variant->options_string ?? '',
                    'sku' => $purchaseItem->variant->sku,
                    'in_stock' => 0,
                    'incoming' => 0,
                    'max_stock_level' => null,
                    'recent_purchase_price' => (float)$recentPurchasePrice,
                    'is_variant' => true,
                    'parent_item_id' => $purchaseItem->item->id,
                ]);
            } else {
                // Get most recent purchase price for this item from this supplier at this location
                $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $purchaseItem->item->id)
                    ->whereNull('variant_id')
                    ->whereHas('purchase', function($query) use ($supplierId, $locationId) {
                        $query->where('supplier_id', $supplierId)
                              ->where('location_id', $locationId);
                    })
                    ->whereNotNull('unit_price')
                    ->where('unit_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('unit_price') ?? 0;
                
                if ($recentPurchasePrice == 0) {
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $purchaseItem->item->id)
                        ->whereNull('variant_id')
                        ->whereHas('purchase', function($query) use ($supplierId, $locationId) {
                            $query->where('supplier_id', $supplierId)
                                  ->where('location_id', $locationId);
                        })
                        ->whereNotNull('purchase_cost')
                        ->where('purchase_cost', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('purchase_cost') ?? 0;
                }
                
                $itemData->push([
                    'id' => $purchaseItem->item->id,
                    'name' => $purchaseItem->item->name,
                    'optionsString' => '',
                    'sku' => $purchaseItem->item->sku,
                    'in_stock' => 0,
                    'incoming' => 0,
                    'max_stock_level' => null,
                    'recent_purchase_price' => (float)$recentPurchasePrice,
                    'is_variant' => false,
                    'parent_item_id' => null,
                ]);
            }
        }
        
        return response()->json($itemData);
    }

    public function getLowStockItems(Request $request)
    {
        $locationId = $request->input('location_id');
        
        if (!$locationId) {
            return response()->json(['error' => 'Location ID is required'], 400);
        }
        
        // Get items with low stock levels at the specific location, excluding composite items
        // quantity < min_stock_level (not <= to avoid items exactly at min level)
        $lowStockItems = \App\Models\StockItem::where('location_id', $locationId)
            ->whereNotNull('min_stock_level')
            ->whereRaw('quantity < min_stock_level')
            ->whereHas('item', function($query) {
                $query->where('is_composite', false);
            })
            ->with(['item', 'variant'])
            ->get();
        
        $itemData = collect();
        
        foreach ($lowStockItems as $stockItem) {
            if ($stockItem->variant) {
                // Get recent purchase price for this variant
                $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $stockItem->variant->id)
                    ->whereNotNull('unit_price')
                    ->where('unit_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('unit_price') ?? 0;
                
                if ($recentPurchasePrice == 0) {
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('variant_id', $stockItem->variant->id)
                        ->whereNotNull('purchase_cost')
                        ->where('purchase_cost', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('purchase_cost') ?? 0;
                }
                
                $itemData->push([
                    'id' => $stockItem->variant->id,
                    'name' => $stockItem->item->name,
                    'optionsString' => $stockItem->variant->options_string ?? '',
                    'sku' => $stockItem->variant->sku,
                    'in_stock' => (float)$stockItem->quantity,
                    'incoming' => 0,
                    'max_stock_level' => $stockItem->max_stock_level ? (float)$stockItem->max_stock_level : null,
                    'recent_purchase_price' => (float)$recentPurchasePrice,
                    'is_variant' => true,
                    'parent_item_id' => $stockItem->item->id,
                ]);
            } else {
                // Get recent purchase price for this item
                $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $stockItem->item->id)
                    ->whereNull('variant_id')
                    ->whereNotNull('unit_price')
                    ->where('unit_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('unit_price') ?? 0;
                
                if ($recentPurchasePrice == 0) {
                    $recentPurchasePrice = \App\Models\PurchaseItem::where('item_id', $stockItem->item->id)
                        ->whereNull('variant_id')
                        ->whereNotNull('purchase_cost')
                        ->where('purchase_cost', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->value('purchase_cost') ?? 0;
                }
                
                $itemData->push([
                    'id' => $stockItem->item->id,
                    'name' => $stockItem->item->name,
                    'optionsString' => '',
                    'sku' => $stockItem->item->sku,
                    'in_stock' => (float)$stockItem->quantity,
                    'incoming' => 0,
                    'max_stock_level' => $stockItem->max_stock_level ? (float)$stockItem->max_stock_level : null,
                    'recent_purchase_price' => (float)$recentPurchasePrice,
                    'is_variant' => false,
                    'parent_item_id' => null,
                ]);
            }
        }
        
        return response()->json($itemData);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required_without:items.*.variant_id|exists:items,id',
            'items.*.variant_id' => 'nullable|exists:item_variants,id',
            'items.*.quantity_ordered' => 'required|numeric|min:1',
            'items.*.purchase_cost' => 'required|numeric|min:0',
            'additional_costs' => 'nullable|array',
        ]);
        $reference = Purchase::generateReference();
        \DB::transaction(function () use ($validated, $reference) {
            $total = 0;
            foreach ($validated['items'] as &$item) {
                // If variant_id is present, get its item_id
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ItemVariant::find($item['variant_id']);
                    if ($variant) {
                        $item['item_id'] = $variant->item_id;
                        // Ensure variant_id is set and not null for variants
                        $item['variant_id'] = $variant->id;
                    }
                } else {
                    // If not a variant, ensure variant_id is null
                    $item['variant_id'] = null;
                }
            }
            unset($item);
            foreach ($validated['items'] as $item) {
                $total += $item['quantity_ordered'] * $item['purchase_cost'];
            }
            $additional_costs = $validated['additional_costs'] ?? [];
            if (!empty($additional_costs)) {
                $total += array_sum(array_column($additional_costs, 'amount'));
            }
            $purchase = Purchase::create([
                'reference' => $reference,
                'supplier_id' => $validated['supplier_id'],
                'location_id' => $validated['location_id'],
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'],
                'notes' => $validated['notes'] ?? null,
                'additional_costs' => $additional_costs,
                'total_amount' => $total,
                'discount' => 0,
                'tax' => 0,
                'total_cost' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'created_by' => auth()->id(),
            ]);
            foreach ($validated['items'] as $item) {
                // Always match both item_id and variant_id for stock items
                $stockItem = \App\Models\StockItem::firstOrCreate([
                    'location_id' => $validated['location_id'],
                    'item_id' => $item['item_id'],
                    'variant_id' => $item['variant_id'],
                ], [
                    'quantity' => 0
                ]);
                // Remove logic that updates in_stock or cost for variants here
                $purchase->items()->create([
                    'stock_item_id' => $stockItem->id,
                    'item_id' => $item['item_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'purchase_cost' => $item['purchase_cost'],
                    'quantity_received' => 0,
                    'status' => 'pending',
                ]);
            }
        });
        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier', 
            'location.locationType', 
            'location.business', 
            'items.item.unit', 
            'items.variant', 
            'items.stockItem', 
            'orderedBy.roles'
        ]);
        $orderedByRole = $purchase->orderedBy && $purchase->orderedBy->roles->count() > 0
            ? $purchase->orderedBy->roles->first()->name
            : null;
        $locationBusinessName = $purchase->location && $purchase->location->business ? $purchase->location->business->name : null;
        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
            'orderedByRole' => $orderedByRole,
            'locationBusinessName' => $locationBusinessName,
        ]);
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['items']);
        $suppliers = Supplier::all();
        $stores = Store::all();
        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'stores' => $stores,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'store_id' => 'required|exists:stores,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.purchase_cost' => 'required|numeric|min:0',
            'additional_costs' => 'nullable|array',
        ]);
        DB::transaction(function () use ($purchase, $data) {
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'store_id' => $data['store_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'],
                'notes' => $data['notes'],
                'additional_costs' => $data['additional_costs'] ?? [],
            ]);
            $purchase->items()->delete();
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $item['quantity_ordered'] * $item['purchase_cost'];
                $purchase->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'purchase_cost' => $item['purchase_cost'],
                    'quantity_received' => 0,
                    'status' => 'pending',
                ]);
            }
            if (!empty($data['additional_costs'])) {
                $total += array_sum($data['additional_costs']);
            }
            $purchase->update(['total_cost' => $total]);
        });
        return redirect()->route('purchases.index');
    }

    public function receive(Request $request, Purchase $purchase)
    {   
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_items,id',
            'items.*.to_receive' => 'required|integer|min:0',
            'additional_costs' => 'nullable|array',
            'additional_costs.*' => 'string',
            'prices' => 'nullable|array', // Accept prices from frontend for missing items
        ]);
        
        $purchase->load(['items', 'items.item', 'items.stockItem']);

        // 0. Check for missing prices in items table for items being received
        $missingPrices = [];
        foreach ($purchase->items as $item) {
            $toReceive = collect($data['items'])->firstWhere('id', $item->id)['to_receive'] ?? 0;
            if ($toReceive > 0 && (is_null($item->item->price) || $item->item->price === '')) {
                $missingPrices[] = [
                    'item_id' => $item->item->id,
                    'name' => $item->item->name,
                    'sku' => $item->item->sku,
                ];
            }
        }
        // If missing prices and not provided from frontend, return Inertia response with errors
        if (count($missingPrices) > 0 && empty($data['prices'])) {
            // If Inertia request, return with errors and missing_prices as props
            if ($request->hasHeader('X-Inertia')) {
                return redirect()->back()->with([
                    'missing_prices' => $missingPrices,
                    'error' => 'Some items are missing prices. Please enter prices to continue.'
                ])->withInput();
            } else {
                // Fallback for non-Inertia requests
                return redirect()->back()->withErrors([
                    'error' => 'Some items are missing prices. Please enter prices to continue.'
                ])->withInput();
            }
        }
        // If prices provided, update items table
        if (!empty($data['prices'])) {
            foreach ($data['prices'] as $itemId => $price) {
                $itemModel = \App\Models\Item::find($itemId);
                if ($itemModel) {
                    $itemModel->price = $price;
                    $itemModel->save();
                }
            }
            // Reload purchase items with updated prices
            $purchase->load(['items', 'items.item', 'items.stockItem']);
        }

        // 1. Calculate which items are being fully received
        $allWillBeFullyReceived = true;
        foreach ($purchase->items as $item) {
            $toReceive = collect($data['items'])->firstWhere('id', $item->id)['to_receive'] ?? 0;
            if (($item->quantity_received + $toReceive) < $item->quantity_ordered) {
                $allWillBeFullyReceived = false;
                break;
            }
        }

        // 2. If all items will be fully received and not all additional costs are checked, warn (frontend should handle confirmation)
        $uncheckedCosts = [];
        $checkedLabels = $data['additional_costs'] ?? [];
        $allCostLabels = collect($purchase->additional_costs ?? [])->pluck('label')->all();
        $uncheckedCosts = array_diff($allCostLabels, $checkedLabels);

        // 3. Apply receiving
        DB::transaction(function () use ($purchase, $data, $checkedLabels) {
            $checkedCosts = collect($purchase->additional_costs ?? [])->whereIn('label', $checkedLabels);
            $totalAdditional = $checkedCosts->sum('amount');
            $totalToReceive = collect($data['items'])->sum('to_receive');

            foreach ($data['items'] as $itemData) {
                $purchaseItem = $purchase->items->firstWhere('id', $itemData['id']);
                $purchaseCost=$purchaseItem->purchase_cost;
                $toReceive = (int) $itemData['to_receive'];
                // Save variant_id if present
                if (array_key_exists('variant_id', $itemData)) {
                    $purchaseItem->variant_id = $itemData['variant_id'];
                }
                if ($toReceive > 0) {
                    if ($purchaseItem->quantity_received + $toReceive > $purchaseItem->quantity_ordered) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.{$itemData['id']}.to_receive" => "Cannot receive more than ordered for item {$purchaseItem->item->name}."
                        ]);
                    }
                    // Update purchase_items
                    $purchaseItem->quantity_received += $toReceive;
                    if ($purchaseItem->quantity_received >= $purchaseItem->quantity_ordered) {
                        $purchaseItem->status = 'received';
                    } else {
                        $purchaseItem->status = 'partially_received';
                    }
                    $proportional = $totalToReceive ? ($toReceive / $totalToReceive) * $totalAdditional : 0;
                    $purchaseItem->proportional_additional_cost += $proportional;
                    $purchaseItem->save();

                    // --- PRICE LOGIC START ---
                    $item = $purchaseItem->item;
                    $stockItem = $purchaseItem->stockItem;
                    $submittedPrice = $data['prices'][$item->id] ?? null;

                    if ($submittedPrice !== null && $submittedPrice !== '') {
                        // User submitted a price: update both item and stock item
                        $item->price = $submittedPrice;
                        $item->save();
                        $stockItem->price = $submittedPrice;
                        
                    } else {
                        // No submitted price: use item price for stock item
                        $stockItem->price = $item->price;
                    }
                    // --- PRICE LOGIC END ---
                    $stockItem->cost = $purchaseCost;
                    $stockItem->quantity += $toReceive;
                    $stockItem->save();

                    // Update items.in_stock
                    $item->in_stock += $toReceive;
                    $item->save();
                    
                    // Update variant in_stock if this is a variant
                    if ($purchaseItem->variant_id) {
                        $variant = \App\Models\ItemVariant::find($purchaseItem->variant_id);
                        if ($variant) {
                            $variant->in_stock += $toReceive;
                            $variant->save();
                        }
                    }
                }
            }

            // Remove applied additional costs from purchase
            if ($checkedLabels) {
                $purchase->additional_costs = collect($purchase->additional_costs ?? [])->reject(fn($cost) => in_array($cost['label'], $checkedLabels))->values()->all();
            }
            // If all items are fully received, mark purchase as completed
            if ($purchase->items->every(fn($item) => $item->quantity_received >= $item->quantity_ordered)) {
                $purchase->status = 'completed';
            } elseif ($purchase->items->some(fn($item) => $item->quantity_received > 0) && !$purchase->items->every(fn($item) => $item->quantity_received >= $item->quantity_ordered)) {
                $purchase->status = 'partially_received';
            } else {
                $purchase->status = 'pending';
            }
            $purchase->save();
        });

        // 4. If all items will be fully received and there are unchecked costs, return a warning (frontend should handle confirmation)
        if ($allWillBeFullyReceived && count($uncheckedCosts) > 0 && !$request->boolean('force_discard_costs')) {
            return response()->json([
                'warning' => 'Some additional costs are not applied and will be discarded. Continue?',
                'unchecked_costs' => array_values($uncheckedCosts),
            ], 409);
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Items received successfully.');
    }

    public function showReceiveForm(Purchase $purchase)
    {
        $purchase->load(['items.item', 'items.stockItem']);
        return Inertia::render('Purchases/Receive', [
            'purchase' => $purchase,
            'items' => $purchase->items,
            'additional_costs' => $purchase->additional_costs ?? [],
        ]);
    }

    public function send(Purchase $purchase)
    {
        // Generate PDF and send email to supplier
        $pdf = Pdf::loadView('pdf.purchase_receipt', ['purchase' => $purchase->load(['supplier', 'items'])]);
        $supplier = $purchase->supplier;
        if ($supplier && $supplier->email) {
            Mail::send([], [], function ($message) use ($supplier, $pdf) {
                $message->to($supplier->email)
                    ->subject('Purchase Order Receipt')
                    ->attachData($pdf->output(), 'purchase_receipt.pdf');
            });
        }
        $purchase->status = 'sent';
        $purchase->save();
        return redirect()->route('purchases.show', $purchase);
    }

    public function sendEmail(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'from' => 'required|email',
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);
        $num=0;

        // Generate PDF
        $pdf = Pdf::loadView('pdf.purchase_receipt', ['purchase' => $purchase->load(['supplier', 'items']),'num'=>$num]);
        $filename = $purchase->reference . '.pdf';

        try {
            Mail::send([], [], function ($message) use ($data, $pdf, $filename) {
                $message->from($data['from'])
                    ->to($data['to'])
                    ->subject($data['subject']);

                if (!empty($data['cc'])) {
                    $ccs = array_filter(array_map('trim', explode(',', $data['cc'])));
                    if (count($ccs)) {
                        $message->cc($ccs);
                    }
                }

                if (!empty($data['message'])) {
                    $message->html(nl2br(e($data['message'])));
                }

                $message->attachData($pdf->output(), $filename);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase order sent by email!');
    }

    public function export(Purchase $purchase, $format)
    {
        // Export as PDF or CSV
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pdf.purchase_receipt', ['purchase' => $purchase->load(['supplier', 'items'])]);
            return $pdf->download('purchase_receipt.pdf');
        } elseif ($format === 'csv') {
            $csv = '';
            foreach ($purchase->items as $item) {
                $csv .= $item->item_id . ',' . $item->quantity_ordered . ',' . $item->purchase_cost . "\n";
            }
            return response($csv)->header('Content-Type', 'text/csv');
        }
        abort(404);
    }

    public function duplicate(Purchase $purchase)
    {
        $new = $purchase->replicate();
        $new->status = 'draft';
        $new->save();
        foreach ($purchase->items as $item) {
            $new->items()->create($item->replicate()->toArray());
        }
        return redirect()->route('purchases.edit', $new);
    }

    public function cancel(Purchase $purchase)
    {
        $purchase->status = 'cancelled';
        $purchase->save();
        return redirect()->route('purchases.show', $purchase);
    }

    public function approve(Purchase $purchase, Request $request)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft orders can be approved.');
        }
        $purchase->status = 'pending';
        $purchase->save();
        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase order approved and is now pending.');
    }
} 