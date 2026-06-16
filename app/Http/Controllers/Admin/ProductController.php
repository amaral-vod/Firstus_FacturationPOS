<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Fournisseur;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $siteId = StockService::resolveSiteId();
        $query = Product::with(['category', 'fournisseur', 'stocks' => fn ($q) => $q->where('site_id', $siteId)]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::where('is_active', true)->get();
        $fournisseurs = Fournisseur::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'fournisseurs', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
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
            $product->stocks()->create([
                'site_id' => StockService::resolveSiteId(),
                'quantity' => 0,
                'min_quantity' => $request->min_quantity ?? 5,
            ]);
        }

        ActivityLogger::log('creation', 'produits', "Création produit {$product->name}");

        return back()->with('success', '✅ Produit ajouté.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
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

        $siteId = StockService::resolveSiteId();
        $current = $product->stockForSite($siteId);

        $product->stocks()->updateOrCreate(
            ['site_id' => $siteId],
            [
                'quantity' => $current?->quantity ?? 0,
                'min_quantity' => $request->input('min_quantity', $current?->min_quantity ?? 5),
                'max_quantity' => $request->input('max_quantity', $current?->max_quantity ?? 0),
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
