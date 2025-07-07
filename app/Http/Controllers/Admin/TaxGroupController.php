<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Auth;

class TaxGroupController extends Controller
{
    public function index()
    {
        $taxGroups = TaxGroup::all();
        return Inertia::render('Admin/TaxGroups/Index', [
            'taxGroups' => $taxGroups,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TaxGroups/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:2',
            'description' => 'required|string',
            'rate' => 'required|numeric|min:0',
        ]);
        TaxGroup::create($validated);
        return redirect()->route('admin.tax-groups.index')->with('success', 'Tax group created.');
    }

    public function edit(TaxGroup $taxGroup)
    {
        $this->authorize('update', $taxGroup);
        return Inertia::render('Admin/TaxGroups/Edit', [
            'taxGroup' => $taxGroup,
        ]);
    }

    public function update(Request $request, TaxGroup $taxGroup)
    {
        $this->authorize('update', $taxGroup);
        $validated = $request->validate([
            'code' => 'required|string|max:2',
            'description' => 'required|string',
            'rate' => 'required|numeric|min:0',
        ]);
        $taxGroup->update($validated);
        return redirect()->route('admin.tax-groups.index')->with('success', 'Tax group updated.');
    }

    public function destroy(TaxGroup $taxGroup)
    {
        $this->authorize('delete', $taxGroup);
        $taxGroup->delete();
        return redirect()->route('admin.tax-groups.index')->with('success', 'Tax group deleted.');
    }
} 