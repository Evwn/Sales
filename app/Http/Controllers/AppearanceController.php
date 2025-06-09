<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AppearanceController extends Controller
{
    public function edit()
    {
        return Inertia::render('settings/Appearance');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
        ]);

        $user = $request->user();
        $user->theme = $validated['theme'];
        $user->save();

        return back();
    }
} 