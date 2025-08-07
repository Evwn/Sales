<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftBalance;
use App\Models\CashDrawerMovement;
use App\Models\TimeClockEntry;

class ShiftController extends Controller
{
    public function open(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'device_uuid' => 'required|string',
        ]);

        // Get branch_id from device
        $device = \App\Models\PosDevice::where('device_uuid', $request->device_uuid)->first();
        if (!$device) {
            return response()->json(['error' => 'Device not found.'], 404);
        }

        $branch_id = $device->branch_id;

        // 1. User must be clocked in
        $clockedIn = TimeClockEntry::where('user_id', auth()->id())
            ->where('branch_id', $branch_id)
            ->whereNull('clock_out')
            ->exists();
        if (!$clockedIn) {
            return response()->json(['error' => 'You must clock in before opening a shift.'], 403);
        }

        // 2. Only one open shift per branch
        $openShift = Shift::where('branch_id', $branch_id)
            ->whereNull('closed_at')
            ->first();
        if ($openShift) {
            return response()->json(['error' => 'A shift is already open for this branch.'], 403);
        }

        // Create the shift
        $shift = Shift::create([
            'branch_id' => $branch_id,
            'user_id' => auth()->id(),
            'opened_at' => now(),
        ]);

        // Create the shift balance record
        ShiftBalance::create([
            'shift_id' => $shift->id,
            'opening_balance' => $request->opening_balance,
            'expected_close_cash' => $request->opening_balance,
        ]);

        // Create the opening cash movement
        CashDrawerMovement::create([
            'shift_id' => $shift->id,
            'user_id' => auth()->id(),
            'type' => 'in',
            'amount' => $request->opening_balance,
            'reason' => 'Opening float',
        ]);

        return response()->json(['success' => true, 'shift_id' => $shift->id]);
    }

    public function close(Request $request)
    {   \Log::info('stock items', [
            'stock_item_ids' => $request,
        ]);
        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'closing_balance' => 'required|numeric|min:0',
            'real_close_cash' => 'required|numeric|min:0',
            'expected_close_cash' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string|max:255',
            'closing_reason' => 'nullable|string|max:255',
        ]);

        $shift = Shift::findOrFail($request->shift_id);

        // 3. Only allow closing if shift is open
        if ($shift->closed_at) {
            return response()->json(['error' => 'Shift is already closed.'], 403);
        }

        // 4. Only allow closing the currently open shift for this branch
        $openShift = Shift::where('branch_id', $shift->branch_id)
            ->whereNull('closed_at')
            ->first();
        if (!$openShift || $openShift->id !== $shift->id) {
            return response()->json(['error' => 'No open shift to close for this branch.'], 403);
        }

        // Update the shift
        $shift->update([
            'closed_at' => now(),
        ]);

        // Update the shift balance
        $shiftBalance = ShiftBalance::where('shift_id', $shift->id)->first();
        if ($shiftBalance) {
            $shiftBalance->update([
                'closing_balance' => $request->closing_balance,
                'real_close_cash' => $request->real_close_cash,
                'expected_close_cash' => $request->expected_close_cash,
                'closing_note' => $request->closing_note,
                'closing_reason' => $request->closing_reason,
            ]);
        }

        // Create the closing cash movement
        CashDrawerMovement::create([
            'shift_id' => $shift->id,
            'user_id' => auth()->id(),
            'type' => 'out',
            'amount' => $request->real_close_cash,
            'reason' => 'Closing cash drop',
        ]);

        return response()->json(['success' => true]);
    }
}