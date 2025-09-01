<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Item;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   $user = auth()->user();
        $quotations = Quotation::with([
            'requisition:id,reference,location_id',
            'requisition.location:id,name',
            'items.item:id,name',
            'supplier:id,name'
        ])->whereBelongsTo( $user)
        ->latest()
        ->get();
        // $Test=User::with('isEmployee')->get();
        //\Log::info($quotations);

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations
        ]);

    }


    // Quotation -> Purchase
// $chosen = $quotation->suppliers()->where('supplier_id',$request->supplier_id)->first();
// $purchase = Purchase::create([...]);

// foreach($chosen->responses as $resp){
//     PurchaseItem::create([
//         'purchase_id'=>$purchase->id,
//         'item_id'=>$resp->item_id,
//         'unit_price'=>$resp->unit_price,
//         'quantity_ordered'=>$resp->quotation->items()->where('item_id',$resp->item_id)->first()->quantity,
//     ]);
// }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        $approvedRequisitions = Requisition::with('items.item', 'location')
            ->where('status', 'approved')
            ->get();

        $suppliers = Supplier::where('branch_id', $user->branch_id)
            ->orderBy('name')
            ->get(['id','name']);
            \Log::info($suppliers);

        return Inertia::render('Quotations/Create', [
            'requisitions' => $approvedRequisitions,
            'items' => Item::select('id','name')->orderBy('name')->get(),
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => ['required','exists:requisitions,id'],
            'quotation_date' => ['required','date'],
            'notes' => ['nullable','string'],
            'suppliers' => ['array','min:1'],
            'suppliers.*' => ['exists:suppliers,id'],
            'items' => ['array'],
            'items.*.item_id' => ['required','exists:items,id'],
            'items.*.quantity' => ['required','numeric','min:0.01'],
        ]);
        \Log::info($validated);

        $req = Requisition::where('id',$validated['requisition_id'])
            ->where('status','approved')
            ->firstOrFail();

        $reference = Quotation::generateReference();

        $quotation = Quotation::create([
            'requisition_id' => $req->id,
            'user_id' => auth()->id(),
            'reference' => $reference,
            'quotation_date' => $validated['quotation_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'total_amount' => collect($validated['items'])
                ->sum(fn($r) => $r['quantity']),
            'valid_until' => now()->addDays(30),
        ]);

        foreach ($validated['items'] as $row) {
            QuotationItem::create([
                'quotation_id'=>$quotation->id,
                'item_id'=>$row['item_id'],
                'quantity'=>$row['quantity'],
                'unit_price'=>0,
            ]);
        }

        foreach ($validated['suppliers'] as $sid) {
            $quotation->suppliers()->attach($sid);
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load([
            'requisition.location:id,name',
            'requisition:id,reference,location_id,requisition_date',
            'items.item:id,name',
            'suppliers:id,name,email'
        ]);
        \Log::info($quotation);
        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['requisition.location:id,name', 'items.item:id,name']);

        return Inertia::render('Quotations/Edit', [
            'quotation' => $quotation,
            'itemsMaster' => Item::select('id','name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'reference' => ['required','string','max:255', Rule::unique('quotations','reference')->ignore($quotation->id)],
            'quotation_date' => ['required','date'],
            'status' => ['required', Rule::in(['draft','sent','approved','rejected'])],
            'notes' => ['nullable','string'],
        ]);

        $quotation->update($validated);

        return redirect()->back()->with('success', 'Quotation updated.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }

    // --- Inline operations ---
    public function updateStatus(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft','sent','approved','rejected'])],
        ]);
        $quotation->update(['status' => $data['status']]);
        return back()->with('success', 'Status updated.');
    }

    public function upsertItem(Request $request, Quotation $quotation)
    {
        $payload = $request->validate([
            'item_id' => ['required','exists:items,id'],
            'quantity' => ['required','numeric','min:0.01'],
            'unit_price' => ['required','numeric','min:0'],
        ]);

        $qi = QuotationItem::withTrashed()
            ->firstOrNew(['quotation_id' => $quotation->id, 'item_id' => $payload['item_id']]);

        $qi->fill($payload);
        $qi->quotation_id = $quotation->id;
        $qi->restore();
        $qi->save();

        return back()->with('success', 'Item saved.');
    }

    public function removeItem(Quotation $quotation, QuotationItem $item)
    {
        abort_unless($item->quotation_id === $quotation->id, 404);
        $item->delete();
        return back()->with('success', 'Item removed.');
    }

     public function sendEmail(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'from' => ['required','email'],
            'suppliers' => ['array'],
            'suppliers.*id' => ['exists:suppliers,id'],
            'suppliers.*email' => ['required','email'],
            'cc' => ['nullable','string'],
            'subject' => ['required','string','max:255'],
            'message' => ['nullable','string'],
        ]);
        $quotationId=$quotation['id'];
        $quotation=Quotation::find($quotationId);


        try {
            foreach($data['suppliers'] as $s){
                $supplierId=$s['id'];
                $supplier=Supplier::find($supplierId);
                $supplierEmail=$s['email'];
            \Mail::to($supplier->email)->send(new \App\Mail\QuotationRequestMail($quotation,$supplier));
            \Log::info('sent');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

       return redirect()->route('quotations.index')->with('success', 'Quotation sent.');
    }
}