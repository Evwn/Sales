<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['pin_code' => 'required']);
        $user = \App\Models\User::where('pin_code', $request->pin_code)->first();
        if ($user) {
            \Auth::login($user);
            session(['pos_login' => true]); // Set POS session flag
            return redirect()->intended('/pos/dashboard');
        }
        return back()->withErrors(['pin_code' => 'Invalid PIN']);
    }

    public function logout(Request $request)
    {   $user = Auth::user();
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
        return redirect('/pos');
    }
}