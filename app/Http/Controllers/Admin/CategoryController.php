<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $category = Category::create($data);

        ActivityLogger::log('creation', 'categories', "Création catégorie {$category->name}");

        return back()->with('success', '✅ Catégorie créée.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $category->update($data);

        return back()->with('success', '✅ Catégorie mise à jour.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', '❌ Impossible de supprimer une catégorie avec des produits.');
        }

        $category->delete();

        return back()->with('success', '🗑️ Catégorie supprimée.');
    }
}
