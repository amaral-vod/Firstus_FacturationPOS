<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'stock']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'required|string|unique:products',
            'barcode' => 'nullable|string|unique:products',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'initial_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
        ]);

        $product = Product::create($data);

        if ($request->filled('initial_stock')) {
            StockService::adjust($product, (int) $request->initial_stock, 'entree', 'INIT', 'Stock initial');
        } else {
            $product->stock()->create(['quantity' => 0, 'min_quantity' => $request->min_quantity ?? 5]);
        }

        ActivityLogger::log('creation', 'produits', "Création produit {$product->name}");

        return back()->with('success', '✅ Produit ajouté.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'required|string|unique:products,sku,'.$product->id,
            'barcode' => 'nullable|string|unique:products,barcode,'.$product->id,
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'min_quantity' => 'nullable|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        unset($data['min_quantity'], $data['max_quantity']);
        $product->update($data);

        $product->stock()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'quantity' => $product->stock?->quantity ?? 0,
                'min_quantity' => $request->input('min_quantity', $product->stock?->min_quantity ?? 5),
                'max_quantity' => $request->input('max_quantity', $product->stock?->max_quantity ?? 0),
            ]
        );

        ActivityLogger::log('modification', 'produits', "Modification produit {$product->name}");

        return back()->with('success', '✅ Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();
        ActivityLogger::log('suppression', 'produits', "Suppression produit {$name}");

        return back()->with('success', '🗑️ Produit supprimé.');
    }
}
