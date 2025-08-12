<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockItem;
use Inertia\Inertia;

class StockAdjustment extends Controller
{
    public function index(){
        $user = auth()->user();

$items = StockItem::with([
        'item',
        'location.business',
        'location.branch',
    ])
    ->whereHas('location', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })
    ->get();

        return Inertia::render('StockAdjustment/Index', [
            'items'=>$items,
        ]);
    }
public function update(Request $request, StockItem $stockItem)
{
    $data = $request->validate([
        'quantity' => 'nullable|numeric',
        'price' => 'nullable|numeric',
        'cost' => 'nullable|numeric',
    ]);

    $stockItem->update($data);

    return response()->json(['success' => true]);
}

}
