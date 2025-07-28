<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\PosDevice;
use App\Models\User;
use App\Models\TimeClockEntry;

class POSController extends Controller
{
    public function loginWithPin(Request $request)
    {
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
    {
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
            
            \Log::info('Composite items found in POS:', [
                'total_items' => $stockItems->count(),
                'composite_items' => $compositeItemsFound->count(),
                'composite_item_names' => $compositeItemsFound->map(function($item) {
                    return [
                        'id' => $item->item->id,
                        'name' => $item->item->name,
                        'is_composite' => $item->item->is_composite,
                        'has_variants' => $item->item->has_variants,
                        'variant_id' => $item->variant_id,
                        'variant_options' => $item->variant ? $item->variant->options : null,
                        'components_count' => $item->item->components->count(),
                        'components_info' => $item->item->components_info ?? []
                    ];
                })->toArray()
            ]);
        } else {
            // If user has no branch_id, return empty collection
            $stockItems = collect();
            
            \Log::info('No branch_id for user, returning empty stock items');
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
} 