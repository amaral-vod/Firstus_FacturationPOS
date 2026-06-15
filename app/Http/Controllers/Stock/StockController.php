<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = Stock::with('product.category')
            ->when($request->alerte, fn ($q) => $q->whereColumn('quantity', '<=', 'min_quantity'))
            ->latest()
            ->paginate(20);

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $lowCount = Stock::whereColumn('quantity', '<=', 'min_quantity')->count();

        return view('stock.index', compact('stocks', 'products', 'lowCount'));
    }

    public function mouvements()
    {
        $mouvements = StockMovement::with(['product', 'user'])->latest()->paginate(30);

        return view('stock.mouvements', compact('mouvements'));
    }

    public function entree(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        StockService::adjust($product, $data['quantity'], 'entree', null, $data['notes'] ?? 'Entrée de stock');
        ActivityLogger::log('entree_stock', 'stock', "Entrée +{$data['quantity']} pour {$product->name}");

        return back()->with('success', '📦 Entrée de stock enregistrée.');
    }

    public function sortie(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            $product = Product::findOrFail($data['product_id']);
            StockService::adjust($product, $data['quantity'], 'sortie', null, $data['notes'] ?? 'Sortie de stock');
            ActivityLogger::log('sortie_stock', 'stock', "Sortie -{$data['quantity']} pour {$product->name}");

            return back()->with('success', '📤 Sortie de stock enregistrée.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ '.$e->getMessage());
        }
    }

    public function inventaire(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        StockService::adjust($product, $data['quantity'], 'inventaire', null, $data['notes'] ?? 'Inventaire');
        ActivityLogger::log('inventaire', 'stock', "Inventaire {$product->name} => {$data['quantity']}");

        return back()->with('success', '📋 Inventaire mis à jour.');
    }
}
