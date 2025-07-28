<?php
namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TaxGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\ItemComponent;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['category', 'taxGroup', 'variants.variantValues.option'])
            ->where('user_id', Auth::id())
            ->get();

        $displayItems = collect();
        foreach ($items as $item) {
            if ($item->is_variant) {
                foreach ($item->variants as $variant) {
                    $displayItems->push([
                        'id' => $variant->id,
                        'name' => $item->name,
                        'options' => $variant->variantValues,
                        'optionsString' => $variant->options_string,
                        'sku' => $variant->sku,
                        'barcode' => $variant->barcode,
                        'price' => $variant->price,
                        'cost' => $variant->cost,
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                        'category' => $item->category,
                        'taxGroup' => $item->taxGroup,
                    ]);
                }
            } else {
                $displayItems->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'options' => [],
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'price' => $item->price,
                    'cost' => $item->cost,
                    'is_variant' => false,
                    'parent_item_id' => null,
                    'category' => $item->category,
                    'taxGroup' => $item->taxGroup,
                ]);
            }
        }
        return Inertia::render('Items/Index', ['items' => $displayItems]);
    }

    public function create()
    {
        $taxGroups = TaxGroup::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();
        // Generate a unique default SKU for this user
        $skuBase = 'SKU-' . strtoupper(substr(Auth::user()->name ?? 'U', 0, 3));
        $lastSku = \App\Models\Item::where('user_id', Auth::id())
            ->where('sku', 'like', "$skuBase%")
            ->orderByDesc('id')
            ->value('sku');
        if ($lastSku && preg_match('/(\d+)$/', $lastSku, $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $nextNum = 1;
        }
        $defaultSku = $skuBase . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $items = \App\Models\Item::with('variants')->where('user_id', Auth::id())->get();

        // Fetch all units (global and owned by user)
        $units = \App\Models\Unit::where(function($q) {
            $q->whereNull('owner_id')->orWhere('owner_id', Auth::id());
        })->where('is_active', 1)->get();

        $compositeSearchItems = [];
        foreach ($items as $item) {
            if ($item->variants->count()) {
                foreach ($item->variants as $variant) {
                    $compositeSearchItems[] = [
                        'id' => $variant->id,
                        'name' => $item->name,
                        'options' => $variant->options,
                        'optionsString' => implode(' / ', array_values($variant->options ?? [])),
                        'sku' => $variant->sku,
                        'is_variant' => true,
                        'parent_item_id' => $item->id,
                        'cost' => $variant->cost, // always use variant cost
                    ];
                }
            } else {
                $compositeSearchItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'options' => [],
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'is_variant' => false,
                    'parent_item_id' => null,
                    'cost' => $item->cost, // use main item cost only if not a variant
                ];
            }
        }
        return Inertia::render('Items/Create', [
            'taxGroups' => $taxGroups,
            'categories' => $categories,
            'defaultSku' => $defaultSku,
            'compositeSearchItems' => $compositeSearchItems,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        // Decode compositeComponents and variant_matrix from JSON if sent as strings
        if ($request->has('compositeComponents') && is_string($request->compositeComponents)) {
            $request->merge([
                'compositeComponents' => json_decode($request->compositeComponents, true) ?? []
            ]);
        }
        if ($request->has('variant_matrix') && is_string($request->variant_matrix)) {
            $request->merge([
                'variant_matrix' => json_decode($request->variant_matrix, true) ?? []
            ]);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'unit_value' => 'nullable|numeric',
            'batch_size' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'track_stock' => 'boolean',
            'is_composite' => 'boolean',
            'in_stock' => 'integer',
            'low_stock' => 'nullable|integer',
            'is_taxable' => 'boolean',
            'tax_group_id' => 'nullable|exists:tax_groups,id',
            'modifiers' => 'array',
            'discounts' => 'array',
            'variant_matrix' => 'nullable',
            'compositeComponents' => 'nullable|array',
            'image' => 'nullable|image|max:2048',
        ]);
        $data['user_id'] = Auth::id();
        if (empty($data['is_taxable'])) {
            $data['tax_group_id'] = null;
        }
        if (isset($data['barcode']) && trim($data['barcode']) === '') {
            $data['barcode'] = null;
        }
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('item-images', 'public');
        }
        // If composite, set cost to sum of components
        if ($data['is_composite'] && $request->has('compositeComponents')) {
            $data['cost'] = collect($request->input('compositeComponents', []))
                ->reduce(fn($sum, $c) => $sum + ($c['cost'] * $c['qty']), 0);
            $data['is_composite'] = 1;
        } else {
            $data['is_composite'] = 0;
        }
        // Set is_variant if variant_matrix is present and not empty
        $variantMatrix = $request->input('variant_matrix', []);
        if (is_string($variantMatrix)) {
            $variantMatrix = json_decode($variantMatrix, true) ?? [];
        }
        $data['is_variant'] = (!empty($variantMatrix) && count($variantMatrix) > 0) ? 1 : 0;

        // Always save batch_size if present
        if ($request->has('batch_size')) {
            $data['batch_size'] = $request->input('batch_size');
        }
        // Transactional save
        \DB::beginTransaction();
        try {
            // 1. Save item
            $item = Item::create($data);
            // 2. Save components if composite
            if ($item->is_composite) {
                $components = $request->input('compositeComponents', []);
                if (count($components) === 0) {
                    throw new \Exception('At least one component is required for a composite item.');
                }
                foreach ($components as $component) {
                    $isVariant = isset($component['is_variant']) && $component['is_variant'];
                    $componentItemId = !$isVariant ? ($component['id'] ?? $component['component_item_id'] ?? null) : null;
                    $componentVariantId = $isVariant ? ($component['id'] ?? $component['component_variant_id'] ?? null) : null;
                    if (!$componentItemId && !$componentVariantId) {
                        throw new \Exception('Component item or variant is required.');
                    }
                    if (!isset($component['qty']) || $component['qty'] === '' || $component['qty'] === null) {
                        throw new \Exception('Component quantity is required.');
                    }
                    ItemComponent::create([
                        'item_id' => $item->id,
                        'component_item_id' => $componentItemId,
                        'component_variant_id' => $componentVariantId,
                        'quantity' => $component['qty'] ?? $component['quantity'],
                        'cost' => $component['cost'],
                    ]);
                }
            }
            // 3. Save variant options/values
            $variantOptions = $request->input('variant_options', []);
            if (is_string($variantOptions)) {
                $variantOptions = json_decode($variantOptions, true) ?? [];
            }
            $variantMatrix = $request->input('variant_matrix', []);
            if (is_string($variantMatrix)) {
                $variantMatrix = json_decode($variantMatrix, true) ?? [];
            }
            $optionNameToId = [];
            $valueKeyToId = [];
            foreach ($variantOptions as $option) {
                if (!isset($option['name']) || !trim($option['name'])) {
                    throw new \Exception('Variant option name is required.');
                }
                $opt = \App\Models\VariantOption::create([
                    'item_id' => $item->id,
                    'name' => $option['name'],
                ]);
                $optionNameToId[$option['name']] = $opt->id;
                foreach ($option['values'] as $val) {
                    if (!trim($val)) {
                        throw new \Exception('Variant value is required.');
                    }
                    $v = \App\Models\VariantValue::create([
                        'variant_option_id' => $opt->id,
                        'value' => $val,
                    ]);
                    $valueKeyToId[$option['name'] . ':' . $val] = $v->id;
                }
            }
            // 4. Save item variants and link values
            foreach ($variantMatrix as $variant) {
                // Always auto-generate unique SKU for variant if missing or not unique
                if (!isset($variant['sku']) || !trim($variant['sku']) || \App\Models\ItemVariant::where('sku', $variant['sku'])->exists()) {
                    $skuBase = $item->sku . '-V';
                    do {
                        $nextNum = mt_rand(1000, 9999);
                        $variant['sku'] = $skuBase . $nextNum;
                    } while (\App\Models\ItemVariant::where('sku', $variant['sku'])->exists());
                }
                $variantBarcode = isset($variant['barcode']) && trim($variant['barcode']) !== '' ? $variant['barcode'] : null;
                if (!empty($variantBarcode)) {
                    $barcodeExists = \App\Models\ItemVariant::where('barcode', $variantBarcode)
                        ->whereHas('item', function($q) { $q->where('user_id', Auth::id()); })
                        ->exists();
                    if ($barcodeExists) {
                        throw new \Exception('Variant barcode must be unique for your account.');
                    }
                }
                $itemVariant = $item->variants()->create([
                    'sku' => $variant['sku'],
                    'barcode' => $variantBarcode,
                    'price' => $variant['price'],
                    'cost' => (isset($variant['cost']) && $variant['cost'] !== '' && $variant['cost'] !== null) ? $variant['cost'] : 0.0,
                    'unit_value' => isset($variant['unit_value']) && $variant['unit_value'] !== '' ? $variant['unit_value'] : null,
                    'options' => $variant['options'] ?? [],
                ]);
                foreach ($variant['options'] as $optionName => $value) {
                    $variantValueId = $valueKeyToId[$optionName . ':' . $value] ?? null;
                    if ($variantValueId) {
                        \App\Models\ItemVariantValue::create([
                            'item_variant_id' => $itemVariant->id,
                            'variant_value_id' => $variantValueId,
                        ]);
                    }
                }
            }
            // 5. Attach modifiers and discounts
            if ($request->has('modifiers')) {
                $item->modifiers()->sync($request->input('modifiers', []));
            }
            if ($request->has('discounts')) {
                $item->discounts()->sync($request->input('discounts', []));
            }
            // Ensure is_variant is correct after saving variants
            $item->is_variant = $item->variants()->count() > 0 ? 1 : 0;
            $item->save();
            \DB::commit();
            if ($request->hasHeader('X-Inertia')) {
                // Inertia expects a location response for full redirect
                return \Inertia\Inertia::location(route('items.index'));
            }
            return redirect()->route('items.index')->with('success', 'Item created successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Item $item)
    {
        $this->authorize('view', $item);

        $taxGroups = TaxGroup::where('user_id', Auth::id())->get();
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();
        $units = \App\Models\Unit::where(function($q) {
            $q->whereNull('owner_id')->orWhere('owner_id', Auth::id());
        })->where('is_active', 1)->get();
        $businessId = Auth::user()->business_id;
        $modifiers = \App\Models\Modifier::where('business_id', $businessId)->get();
        // $discounts = \App\Models\Discount::where('user_id', Auth::id())->get();
        $discounts = $item->discounts ?? collect();

        // For composite search items (same logic as in create)
        $items = \App\Models\Item::with('variants')->where('user_id', Auth::id())->get();
        $compositeSearchItems = [];
        foreach ($items as $itm) {
            if ($itm->variants->count()) {
                foreach ($itm->variants as $variant) {
                    $compositeSearchItems[] = [
                        'id' => $variant->id,
                        'name' => $itm->name,
                        'options' => $variant->options,
                        'optionsString' => implode(' / ', array_values($variant->options ?? [])),
                        'sku' => $variant->sku,
                        'is_variant' => true,
                        'parent_item_id' => $itm->id,
                        'cost' => $variant->cost,
                    ];
                }
            } else {
                $compositeSearchItems[] = [
                    'id' => $itm->id,
                    'name' => $itm->name,
                    'options' => [],
                    'optionsString' => '',
                    'sku' => $itm->sku,
                    'is_variant' => false,
                    'parent_item_id' => null,
                    'cost' => $itm->cost,
                ];
            }
        }

        $item->load(['taxGroup', 'modifiers', 'discounts']);

        return Inertia::render('Items/Edit', [
            'item' => $item,
            'categories' => $categories,
            'units' => $units,
            'taxGroups' => $taxGroups,
            'modifiers' => $modifiers,
            'discounts' => $discounts,
            'compositeSearchItems' => $compositeSearchItems,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $this->authorize('update', $item);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'sku' => 'nullable|string|max:255|unique:items,sku,' . $item->id,
            'barcode' => 'nullable|string|max:255|unique:items,barcode,' . $item->id,
            'track_stock' => 'boolean',
            'is_composite' => 'boolean',
            'in_stock' => 'integer',
            'low_stock' => 'nullable|integer',
            'is_taxable' => 'boolean',
            'tax_group_id' => 'nullable|exists:tax_groups,id',
        ]);
        if (empty($data['is_taxable'])) {
            $data['tax_group_id'] = null;
        }
        // For main item barcode, ensure null if blank
        if (isset($data['barcode']) && trim($data['barcode']) === '') {
            $data['barcode'] = null;
        }
        $item->update($data);
        // Ensure is_variant is correct after saving variants (if variants are managed here or elsewhere)
        $item->is_variant = $item->variants()->count() > 0 ? 1 : 0;
        $item->save();
        return redirect()->route('items.index');
    }

    public function show(Item $item)
    {
        $this->authorize('view', $item);
        $item->load('taxGroup');
        return Inertia::render('Items/Show', [
            'item' => $item
        ]);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $userId = auth()->id();

        $items = \App\Models\Item::with(['variants'])
            ->where('user_id', $userId)
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('sku', 'like', "%$query%") ;
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $results = collect();
        foreach ($items as $item) {
            if ($item->is_variant) {
                foreach ($item->variants as $variant) {
                    $results->push([
                        'id' => $variant->id,
                        'name' => $item->name,
                        'optionsString' => $variant->options_string,
                        'sku' => $variant->sku,
                        'in_stock' => $variant->in_stock ?? 0,
                        'cost' => $variant->cost,
                        'is_composite' => $item->is_composite,
                        'parent_item_id' => $item->id,
                        'is_variant' => true,
                    ]);
                }
            } else {
                $results->push([
                    'id' => $item->id,
                    'name' => $item->name,
                    'optionsString' => '',
                    'sku' => $item->sku,
                    'in_stock' => $item->in_stock ?? 0,
                    'cost' => $item->cost,
                    'is_composite' => $item->is_composite,
                    'parent_item_id' => null,
                    'is_variant' => false,
                ]);
            }
        }
        return response()->json($results);
    }

    // Stubs for advanced features (variants, components, etc.)
    // public function variants(Item $item) { }
    // public function components(Item $item) { }
    // public function modifiers(Item $item) { }
    // public function discounts(Item $item) { }
} 