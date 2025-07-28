<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return Inertia::render('Categories/Index', ['categories' => $categories]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category = \App\Models\Category::create([
            'name' => $validated['name'],
            'user_id' => auth()->id(),
        ]);
        return response()->json($category);
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'business_id' => 'required|exists:businesses,id',
        ]);
        $category->update($data);
        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index');
    }
} 