<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\ActivityLogger;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => true,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Log the login activity
        ActivityLogger::logUserLoggedIn(Auth::user());

        $user = Auth::user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->intended(route('users.index'));
        }
        if ($user && $user->hasRole('cashier')) {
            return redirect()->intended('/pos/dashboard');
        }
        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Use the default guard or a dynamic guard instead of hardcoded 'web'
        Auth::guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
