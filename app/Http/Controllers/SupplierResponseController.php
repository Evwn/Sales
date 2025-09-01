<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Item;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierResponseController extends Controller
{
     public function showForm($token)
    {
        $quotation = Quotation::with('quotationItems')->where('response_token', $token)->firstOrFail();

        return Inertia::render('Suppliers/RespondQuotation', [
            'quotation' => $quotation,
            'supplier' => auth()->user()?->supplier // if authenticated supplier
        ]);
    }

    public function store(Request $request, $token)
    {
        $quotation = Quotation::where('response_token', $token)->firstOrFail();

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.quotation_item_id' => 'required|exists:quotation_items,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $response = SupplierResponse::create([
            'quotation_id' => $quotation->id,
            'supplier_id' => $data['supplier_id'],
            'total_amount' => collect($data['items'])->sum(fn($i) => $i['unit_price'] * $i['quantity']),
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $i) {
            $response->items()->create([
                'quotation_item_id' => $i['quotation_item_id'],
                'unit_price' => $i['unit_price'],
                'quantity' => $i['quantity'],
                'line_total' => $i['unit_price'] * $i['quantity'],
            ]);
        }

        return redirect()->route('supplier.response.thankyou');
    }

    public function thankYou()
    {
        return Inertia::render('Suppliers/ResponseThankYou');
    }
}
