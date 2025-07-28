<?php
namespace App\Http\Controllers;

use App\Models\Modifier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModifierController extends Controller
{
    public function index()
    {
        $modifiers = Modifier::all();
        return Inertia::render('Modifiers/Index', ['modifiers' => $modifiers]);
    }

    public function create()
    {
        return Inertia::render('Modifiers/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'required|exists:businesses,id',
        ]);
        Modifier::create($data);
        return redirect()->route('modifiers.index');
    }

    public function edit(Modifier $modifier)
    {
        return Inertia::render('Modifiers/Edit', ['modifier' => $modifier]);
    }

    public function update(Request $request, Modifier $modifier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'required|exists:businesses,id',
        ]);
        $modifier->update($data);
        return redirect()->route('modifiers.index');
    }

    public function destroy(Modifier $modifier)
    {
        $modifier->delete();
        return redirect()->route('modifiers.index');
    }
} 