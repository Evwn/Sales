<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\PosDevice;
use App\Models\User;
use App\Models\TimeClockEntry;
use App\Models\PosTicket;
use App\Models\PosTicketItem;
use App\Models\PosTicketPayment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReceipt;
use App\Models\SalesReceiptItem;
use App\Models\StockItem;
use App\Models\Payment;

class POSController extends Controller
{
    public function loginWithPin(Request $request)
    {   \Log::info('PosController', [
            'Login with pin ' => $request,
        ]);
        $request->validate([
            'pin_code' => 'required|digits:4',
            'device_uuid' => 'required|string',
        ]);
        $device = PosDevice::where('device_uuid', $request->device_uuid)->first();
        if (!$device) {
            return back()->withErrors(['error' => 'Device not registered.']);
        }
        if ($device->is_disabled) {
            return back()->withErrors(['error' => 'This device has been disabled due to too many failed attempts. Please contact your administrator.']);
        }
        $user = User::where('pin_code', $request->pin_code)
            ->where('business_id', $device->business_id)
            ->where('branch_id', $device->branch_id)
            ->first();
        if (!$user) {
            $device->attempts += 1;
            if ($device->attempts >= 3) {
                $device->is_disabled = true;
            }
            $device->save();
            $remaining = max(0, 3 - $device->attempts);
            $msg = $device->is_disabled
                ? 'This device has been disabled due to too many failed attempts. Please contact your administrator.'
                : ($remaining > 0
                    ? 'Invalid PIN. You have ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.'
                    : 'Invalid PIN.');
            return back()->withErrors(['error' => $msg]);
        }
        // Success: reset attempts
        $device->attempts = 0;
        $device->save();
        Auth::login($user);
        session(['pos_login' => true]); // Set POS session flag
        session(['device_uuid' => $request->device_uuid]); // Store device UUID in session
        // Create time clock entry if not already clocked in
        $alreadyClockedIn = TimeClockEntry::where('user_id', $user->id)
            ->where('branch_id', $device->branch_id)
            ->whereNull('clock_out')
            ->exists();
        if (!$alreadyClockedIn) {
            TimeClockEntry::create([
                'user_id' => $user->id,
                'branch_id' => $device->branch_id,
                'clock_in' => now(),
            ]);
        }
        return redirect('/pos/dashboard');
    }

    public function verifyPin(Request $request)
    {   \Log::info('PosController', [
            'verify pin' => $request,
        ]);
        $request->validate([
            'pin_code' => 'required|digits:4',
            'device_uuid' => 'required|string',
        ]);
        $device = \App\Models\PosDevice::where('device_uuid', $request->device_uuid)->first();
        if (!$device || $device->is_disabled) {
            if ($request->hasHeader('X-Inertia')) {
                return redirect()->back()->with('error', 'Device not registered or disabled.');
            }
            return response()->json(['error' => 'Device not registered or disabled.'], 403);
        }
        $user = \App\Models\User::where('pin_code', $request->pin_code)
            ->where('business_id', $device->business_id)
            ->where('branch_id', $device->branch_id)
            ->first();
        if (!$user) {
            if ($request->hasHeader('X-Inertia')) {
                return redirect()->back()->with('error', 'PIN incorrect.');
            }
            return response()->json(['error' => 'PIN incorrect.'], 401);
        }
        // Success: don't log in, just return OK
        if ($request->hasHeader('X-Inertia')) {
            return redirect()->back()->with(['success' => 'PIN correct.', 'userName' => $user->name]);
        }
        return response()->json(['success' => true, 'userName' => $user->name]);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get stock items for all locations in the user's branch, regardless of location type
        $stockItems = collect();
        
        if ($user->branch_id) {
            // Get all stock items for the branch including composite items and variants
            $stockItems = \App\Models\StockItem::with([
                'item.category', 
                'item.components.componentItem',
                'item.components.componentVariant',
                'variant'
            ])
                ->whereHas('location', function($query) use ($user) {
                    $query->where('branch_id', $user->branch_id);
                })
            ->where('quantity', '>', 0)
            ->get();
            $StockItemids = $stockItems->pluck('id')->toArray();
                
            // Add flags to stock items
            $stockItems = $stockItems->map(function ($stockItem) {
                // Add flags to help frontend identify item types
                if ($stockItem->variant_id) {
                    // This is a variant
                    $stockItem->item->is_variant = true;
                    $stockItem->item->is_composite = $stockItem->variant->item->is_composite;
                    $stockItem->item->has_variants = $stockItem->variant->item->variants()->count() > 0;
                } else {
                    // This is a main item
                    $stockItem->item->is_variant = false;
                    $stockItem->item->is_composite = $stockItem->item->is_composite;
                    $stockItem->item->has_variants = $stockItem->item->variants()->count() > 0;
                }
                
                // Add component information for composite items (if not already added)
                if ($stockItem->item->is_composite && !isset($stockItem->item->components_info)) {
                    $stockItem->item->components_info = $stockItem->item->components->map(function($component) {
                        return [
                            'id' => $component->id,
                            'quantity' => $component->quantity,
                            'cost' => $component->cost,
                            'component_item' => $component->componentItem ? [
                                'id' => $component->componentItem->id,
                                'name' => $component->componentItem->name,
                                'price' => $component->componentItem->price,
                            ] : null,
                            'component_variant' => $component->componentVariant ? [
                                'id' => $component->componentVariant->id,
                                'options' => $component->componentVariant->options,
                                'price' => $component->componentVariant->price,
                                'item_name' => $component->componentVariant->item->name,
                            ] : null,
                        ];
                    });
                }
                
                return $stockItem;
            });
                
            // Debug: Log composite items found
            $compositeItemsFound = $stockItems->filter(function($stockItem) {
                return $stockItem->item->is_composite;
            });
            

        } else {
            // If user has no branch_id, return empty collection
            $stockItems = collect();
            

        }
        
        // Get categories for filtering
        $categories = \App\Models\Category::pluck('name')->toArray();
        
        // Get customers for the current user's business/branch
        $customers = collect();
        if ($user->branch_id) {
            $customers = \App\Models\Customer::where('branch_id', $user->branch_id)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone']);
        }
        
        // Check if there's an open shift for this user's branch
        $openShift = null;
        $shiftIsOpen = false;
        $shiftId = null;
        $shiftNumber = 0;
        $cashDrawerData = [
            'starting_cash' => 0,
            'cash_payments' => 0,
            'cash_refunds' => 0,
            'paid_in' => 0,
            'paid_out' => 0,
            'expected_cash_amount' => 0
        ];
        
        if ($user->branch_id) {
            $openShift = \App\Models\Shift::where('branch_id', $user->branch_id)
                ->whereNull('closed_at')
                ->first();
            
            if ($openShift) {
                $shiftIsOpen = true;
                $shiftId = $openShift->id;
                
                // Get shift balance data
                $shiftBalance = \App\Models\ShiftBalance::where('shift_id', $openShift->id)->first();
                if ($shiftBalance) {
                    $cashDrawerData['starting_cash'] = $shiftBalance->opening_balance;
                    $cashDrawerData['expected_cash_amount'] = $shiftBalance->opening_balance;
                }
                
                // Get cash drawer movements
                $cashMovements = \App\Models\CashDrawerMovement::where('shift_id', $openShift->id)->get();
                
                foreach ($cashMovements as $movement) {
                    if ($movement->type === 'in') {
                        if ($movement->reason === 'Opening float') {
                            // Already counted in starting cash
                        } elseif (str_contains(strtolower($movement->reason), 'payment')) {
                            $cashDrawerData['cash_payments'] += $movement->amount;
                        } else {
                            $cashDrawerData['paid_in'] += $movement->amount;
                        }
                    } else { // type === 'out'
                        if (str_contains(strtolower($movement->reason), 'refund')) {
                            $cashDrawerData['cash_refunds'] += $movement->amount;
                        } else {
                            $cashDrawerData['paid_out'] += $movement->amount;
                        }
                    }
                }
                
                // Calculate expected cash amount
                $cashDrawerData['expected_cash_amount'] = $cashDrawerData['starting_cash'] + 
                    $cashDrawerData['cash_payments'] - 
                    $cashDrawerData['cash_refunds'] + 
                    $cashDrawerData['paid_in'] - 
                    $cashDrawerData['paid_out'];
            }
            
            // Get the current time clock entry for this user
            $currentTimeClock = \App\Models\TimeClockEntry::where('user_id', $user->id)
                ->where('branch_id', $user->branch_id)
                ->whereNull('clock_out')
                ->first();
            
            if ($currentTimeClock) {
                // Count all shifts (opened and closed) for this user during their current time clock session
                $shiftNumber = \App\Models\Shift::where('user_id', $user->id)
                    ->where('branch_id', $user->branch_id)
                    ->where('opened_at', '>=', $currentTimeClock->clock_in)
                    ->count();
            }
        }
                                            // Debug logging
        \Log::info('stock items', [
            'shiftIsOpen' => $shiftIsOpen,
            'shiftId' => $shiftId,
            'openShift' => $openShift,
            'shiftNumber' => $shiftNumber,
            'cashDrawerData' => $cashDrawerData,
            'userBranchId' => $user->branch_id,
            'userId' => $user->id,
        ]);
        return Inertia::render('POS/Index', [
            'stockItems' => $stockItems,
            'categories' => $categories,
            'customers' => $customers,
            'shiftIsOpen' => $shiftIsOpen,
            'shiftId' => $shiftId,
            'openShift' => $openShift,
            'shiftNumber' => $shiftNumber,
            'cashDrawerData' => $cashDrawerData,
        ]);
    }

    /**
     * Store a new POS ticket and its items/payments when user clicks CHARGE
     */
    public function storeTicket(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.stock_item_id' => 'required|integer',
            'items.*.qty' => 'required|numeric',
            'items.*.price' => 'required|numeric',
            'items.*.subtotal' => 'required|numeric',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|string',
            'payments.*.amount' => 'required|numeric',
            'payments.*.status' => 'required|string',
            'payments.*.meta' => 'nullable|array',
            'total_amount' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'amount_due' => 'required|numeric',
        ]);

        $user = auth()->user();
        $branch_id = $user->branch_id;

        // Create the ticket
        $ticket = PosTicket::create([
            'user_id' => $user->id,
            'branch_id' => $branch_id,
            'status' => $request->amount_due <= 0 ? 'completed' : 'active',
            'total_amount' => $request->total_amount,
            'amount_paid' => $request->amount_paid,
            'amount_due' => $request->amount_due,
            'payment_details' => $request->payments,
        ]);

        // Save items
        foreach ($request->items as $item) {
            $stockItem = \App\Models\StockItem::find($item['stock_item_id']);

            PosTicketItem::create([
                'ticket_id' => $ticket->id,
                'item_id' => $stockItem->item_id,
                'variant_id' => $item['variant_id'] ?? null,
                'stock_item_id' => $stockItem->id,
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        // Save payments with rounded amounts
        foreach ($request->payments as $payment) {
            $roundedAmount = round($payment['amount']);
            PosTicketPayment::create([
                'ticket_id' => $ticket->id,
                'method' => $payment['method'],
                'amount' => $roundedAmount,
                'status' => $payment['status'],
                'meta' => $payment['meta'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'ticket_id' => $ticket->id, 'status' => $ticket->status]);
    }

    /**
     * Fetch a ticket by ID with items and payments
     */
    public function getTicket($id)
    {
        $ticket = PosTicket::with(['items.item', 'items.variant', 'payments'])->findOrFail($id);
        
        // Format the response to match frontend expectations
        $formattedTicket = [
            'id' => $ticket->id,
            'total_amount' => $ticket->total_amount,
            'amount_paid' => $ticket->amount_paid,
            'amount_due' => $ticket->amount_due,
            'status' => $ticket->status,
            'items' => $ticket->items->map(function($item) {
                $itemName = $item->item->name ?? 'Unknown Item';
                if ($item->variant && $item->variant->name) {
                    $itemName .= ' - ' . $item->variant->name;
                }
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'variant_id' => $item->variant_id,
                    'stock_item_id' => $item->stock_item_id,
                    'name' => $itemName,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'payments' => $ticket->payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'method' => $payment->method,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'meta' => $payment->meta,
                ];
            }),
        ];
        
        return response()->json($formattedTicket);
    }

    public function updateTicketPayment(Request $request, $id)
    {
        $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|string',
            'payments.*.amount' => 'required|numeric',
            'payments.*.status' => 'required|string',
            'payments.*.meta' => 'nullable|array',
        ]);

        $ticket = PosTicket::findOrFail($id);
        
        // Clear existing payments
        $ticket->payments()->delete();
        
        // Add new payments with rounded amounts
        foreach ($request->payments as $payment) {
            $roundedAmount = round($payment['amount']);
            PosTicketPayment::create([
                'ticket_id' => $ticket->id,
                'method' => $payment['method'],
                'amount' => $roundedAmount,
                'status' => $payment['status'],
                'meta' => $payment['meta'] ?? null,
            ]);
        }
        
        // Update ticket totals
        $totalPaid = $ticket->payments()->where('status', 'completed')->sum('amount');
        $amountDue = $ticket->total_amount - $totalPaid;
        $status = $amountDue <= 0 ? 'completed' : 'active';
    
        
        $ticket->update([
            'amount_paid' => $totalPaid,
            'amount_due' => $amountDue,
            'status' => $status,
        ]);
        
        return response()->json([
            'success' => true,
            'ticket' => $this->getTicket($id)->getData()
        ]);
    }

    /**
     * Update an existing ticket with new items and totals
     */
    public function updateTicket(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.stock_item_id' => 'required|integer',
            'items.*.qty' => 'required|numeric',
            'items.*.price' => 'required|numeric',
            'items.*.subtotal' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'amount_paid' => 'required|numeric',
            'amount_due' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $ticket = PosTicket::findOrFail($id);
        
        // Clear existing items
        $ticket->items()->delete();
        
        // Add new items
        foreach ($request->items as $item) {
                        
            $stockItem = \App\Models\StockItem::find($item['stock_item_id']);

            PosTicketItem::create([
                'ticket_id' => $ticket->id,
                'item_id' => $stockItem->item_id,
                'stock_item_id' => $stockItem->id,
                'variant_id' => $item['variant_id'] ?? null,
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);
        }
        
        // Update ticket totals and status
        $ticket->update([
            'total_amount' => $request->total_amount,
            'amount_paid' => $request->amount_paid,
            'amount_due' => $request->amount_due,
            'status' => $request->status,
        ]);
        
        return response()->json([
            'success' => true,
            'ticket' => $this->getTicket($id)->getData()
        ]);
    }

    /**
     * List all active tickets with items and payments
     */
    public function listActiveTickets()
    {
        $tickets = PosTicket::with(['items', 'payments'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($tickets);
    }

    public function logout(Request $request)
    {   \Log::info('PosController', [
            'logout' => $request,
        ]);
        // Clock out the user if they have an open time clock entry
        $user = Auth::user();
        if ($user && $user->branch_id) {
            $openClock = \App\Models\TimeClockEntry::where('user_id', $user->id)
                ->where('branch_id', $user->branch_id)
                ->whereNull('clock_out')
                ->first();
            if ($openClock) {
                $openClock->clock_out = now();
                $openClock->save();
            }
            // Close all open shifts for this branch
            \App\Models\Shift::where('branch_id', $user->branch_id)
                ->whereNull('closed_at')
                ->update(['closed_at' => now()]);
        }
        Auth::logout();
        $request->session()->forget('pos_login');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/pos/login');
    }

    public function cancelTicket($ticketId)
    {
        $ticket = PosTicket::findOrFail($ticketId);
        $ticket->status = 'cancelled';
        $ticket->save();

        return redirect()->route('pos.index')->with('success', 'Ticket cancelled successfully');
    }

        /**
     * Convert a completed POS ticket into a sale record
     */
    public function convertTicketToSale(Request $request, $ticketId)
    {
        try {
            $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
            ]);

            $ticket = PosTicket::with(['items.item', 'items.variant', 'payments'])->findOrFail($ticketId);

            // Only allow conversion of completed tickets
            if ($ticket->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only completed tickets can be converted to sales. Current status: ' . $ticket->status
                ], 400);
            }

            $user = auth()->user();
        
        // Create the sale record
        $sale = Sale::create([
            'reference' => 'SALE-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'customer_id' => $request->customer_id,
            'seller_id' => $user->id,
            'business_id' => $user->business_id,
            'branch_id' => $user->branch_id,
            'amount' => $ticket->total_amount,
            'discount' => 0, 
            'tax' => 0,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_methods' => $ticket->payments->groupBy('method')->map(function($payments) {
                return $payments->sum('amount');
            })->toArray(),
            'sale_date' => now(),
        ]);

        // Create sale items
        $saleItemsCreated = 0;
        foreach ($ticket->items as $ticketItem) {
            $stockItem = \App\Models\StockItem::find($ticketItem->stock_item_id);
            if ($stockItem) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'stock_item_id' => $ticketItem->stock_item_id,
                    'quantity' => $ticketItem->qty,
                    'unit_price' => $ticketItem->price,
                    'discount' => 0, // You can add discount logic if needed
                    'tax' => 0, // You can add tax logic if needed
                ]);
                $saleItemsCreated++;
            } else {
                // Log error if stock item not found
                \Log::error('Stock item not found for item_id: ' . $ticketItem->item_id . ' and location_id: ' . $user->branch_id);
            }
        }

        // Create payment records
        $paymentsCreated = 0;
        foreach ($ticket->payments as $ticketPayment) {
            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $ticketPayment->amount,
                'method' => $ticketPayment->method,
                'reference' => $ticketPayment->meta['reference'] ?? 'PAY-' . date('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'status' => $ticketPayment->status,
                'date' => now(),
                'business_id' => $user->business_id,
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
            ]);
            $paymentsCreated++;
        }

        // Create sales receipt
        $receipt = SalesReceipt::create([
            'reference' => 'REC-' . date('Ymd') . '-' . str_pad(SalesReceipt::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'sale_id' => $sale->id,
            'business_id' => $user->business_id,
            'branch_id' => $user->branch_id,
            'customer_id' => $request->customer_id,
            'cashier_id' => $user->id,
            'subtotal' => $sale->amount,
            'discount' => $sale->discount,
            'tax' => $sale->tax,
            'total' => $sale->amount,
            'total_quantity' => $ticket->items->sum('qty'),
            'payment_methods' => $sale->payment_methods,
            'payment_status' => 'paid',
        ]);

        // Create sales receipt items
        $receiptItemsCreated = 0;
        foreach ($ticket->items as $ticketItem) {
  
            $stockItem = \App\Models\StockItem::find($ticketItem->stock_item_id);
            if ($stockItem) {
                SalesReceiptItem::create([
                    'sales_receipt_id' => $receipt->id,
                    'stock_item_id' =>$ticketItem->stock_item_id,
                    'quantity' => $ticketItem->qty,
                    'unit_price' => $ticketItem->price,
                    'subtotal' => $ticketItem->subtotal,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $ticketItem->subtotal,
                ]);
                $receiptItemsCreated++;
            } else {
                // Log error if stock item not found
                \Log::error('Stock item not found for receipt item_id: ' . $ticketItem->item_id . ' and location_id: ' . $user->branch_id);
            }
        }

        // Mark the ticket as converted
        $ticket->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'sale' => $sale->load(['items', 'payments', 'customer', 'seller', 'business', 'branch']),
            'receipt' => $receipt->load(['items']),
            'message' => 'Ticket successfully converted to sale'
        ]);
        
        } catch (\Exception $e) {
            \Log::error('Error converting ticket to sale', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to convert ticket to sale: ' . $e->getMessage()
            ], 500);
        }
    }
}