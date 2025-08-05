<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    {
        \Auth::logout();
        $request->session()->forget('pos_login');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/pos/login');
    }
}