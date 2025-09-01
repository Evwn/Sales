<?php

namespace App\Http\Controllers;
use App\Models\QuotationSupplier;
use App\Models\QuotationItems;
use App\Models\Quotation;
use App\Models\QuotationResponse;
use Illuminate\Http\Request;

class QuotationResponseController extends Controller
{
        public function form(QuotationSupplier $qs) {
        $quotation = Quotation::with(['quotationItems','quotationItems.item'])->where('id', $qs->quotation_id)->firstOrFail();
        
        return inertia('Suppliers/RespondQuotation', [
            'quotation'=>$quotation,
            'supplier'=>$qs
        ]);
    }

    public function submit(Request $request) {
        $validated = $request->validate([
            'items'=>'required|array',
            'items.*.id'=>'nullable',
            'items.*.unit_price'=>'nullable',
            'supplier_id'=>'required',
            'notes'=>'nullable'
        ]);
        \Log::info($validated);
        $qs=QuotationSupplier::find($validated['supplier_id']);
        \Log::info($qs);
        foreach ($validated['items'] as $row) {
            QuotationResponse::updateOrCreate([
                'quotation_supplier_id'=>$qs->id,
                'item_id'=>$row['id'],
                'unit_price'=>$row['unit_price']]
            );
        }

        $qs->update(['status'=>'responded']);
        return redirect()->route('supplier.response.thankyou');
    }
}
