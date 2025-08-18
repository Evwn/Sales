<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RequisitionController extends Controller
{
public function index()
{   $user=auth()->user();
    $requisitions = Requisition::with(['location', 'requester', 'approver', 'items.item'])
        ->where('user_id', auth()->id())
        ->latest()
        ->get()
        ->map(function ($requisition) {
            if ($requisition->status === 'pending') {
                $requisition->status = 'submitted';
            }
            return $requisition;
        });
    $items = \App\Models\Item::where('user_id', $user->id)
        ->orderBy('name')
        ->get();

    return Inertia::render('Requisitions/Index', [
        'requisitions' => $requisitions,
        'items' => $items,
    ]);
}


    public function create()
    {
        return Inertia::render('Requisitions/Create', [
            'items' => Item::all(['id', 'name']),
            'locations' => Location::all(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'requisition_date' => 'nullable|date',
            'priority' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved,rejected,converted',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id();
        $data['reference'] = \App\Models\Requisition::generateReference();

        $requisition = Requisition::create($data);

        foreach ($data['items'] as $item) {
            $requisition->items()->create($item);
        }

        return redirect()->route('requisitions.index')->with('success', 'Requisition created.');
    }

    public function edit(Requisition $requisition)
    {
        $this->authorize('update', $requisition);

        return Inertia::render('Requisitions/Edit', [
            'requisition' => $requisition->load('items.item'),
            'items' => Item::all(['id', 'name']),
            'locations' => Location::all(['id', 'name'])
        ]);
    }

    public function update(Request $request, Requisition $requisition)
    {
        $this->authorize('update', $requisition);

        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'reference' => 'required|unique:requisitions,reference,' . $requisition->id,
            'requisition_date' => 'nullable|date',
            'priority' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,pending,approved,rejected,converted',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'nullable|string|max:255',
        ]);

        $requisition->update($data);

        $requisition->items()->delete();
        foreach ($data['items'] as $item) {
            $requisition->items()->create($item);
        }

        return redirect()->route('requisitions.index')->with('success', 'Requisition updated.');
    }

    public function destroy(Requisition $requisition)
    {
        $this->authorize('delete', $requisition);

        $requisition->delete();

        return redirect()->route('requisitions.index')->with('success', 'Requisition deleted.');
    }
public function updateStatus(Requisition $requisition, Request $request)
{
    $request->validate([
        'status' => 'required|in:draft,submitted,approved,rejected,converted',
    ]);

    // Map submitted → pending
    $status = $request->status === 'submitted' ? 'pending' : $request->status;

    $requisition->update(['status' => $status]);

    return back()->with('success', 'Status updated.');
}


public function updateItems(Requisition $requisition, Request $request)
{
    \Log::info($requisition->toArray());
    \Log::info($request->all());

    $data = $request->all();
    \Log::info($validated);

    // Normalize into items array
    if (isset($data['item_id']) && isset($data['quantity'])) {
        $data['items'] = [[
            'item_id'  => $data['item_id'],
            'quantity' => $data['quantity'],
            'unit'     => $data['unit'] ?? null,
        ]];
    }
    \Log::info($data);

    $validated = validator($data, [
        'items' => 'required|array',
        'items.*.item_id' => 'required|exists:items,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit' => 'nullable|string|max:50',
    ])->validate();

    foreach ($data['items'] as $itemData) {
        $existing = $requisition->items()
            ->where('item_id', $itemData['item_id'])
            ->first();

        if ($existing) {
            // ✅ Update existing item
            \Log::info('In existing');
            $existing->update([
                'quantity' => $itemData['quantity'],
                'unit'     => $itemData['unit'] ?? $existing->unit,
            ]);
        } else {
            // ✅ Add new item
            \Log::info('In Doesnt exist');
            $requisition->items()->create([
                'item_id'  => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit'     => $itemData['unit'] ?? null,
            ]);
        }
    }

    return back()->with('success', 'Items updated/added successfully.');
}




public function destroyItem(Requisition $requisition, $itemId)
{ 
    $requisitionItem = $requisition->items()->where('id', $itemId)->firstOrFail();

    $requisitionItem->delete();

    return back()->with('success', 'Item removed.');
}



}
