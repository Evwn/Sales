<?php

namespace App\Http\Controllers;

use App\Models\CashDrawerMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashDrawerMovementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $movement = CashDrawerMovement::create([
            'shift_id' => $request->shift_id,
            'type' => $request->type,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'movement' => $movement,
        ]);
    }
} 