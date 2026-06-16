<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use App\Services\StockAnalyticsService;
use App\Services\StockImportService;
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
        $stats = StockAnalyticsService::summary();

        return view('stock.index', compact('stocks', 'products', 'lowCount', 'stats'));
    }

    public function analyse()
    {
        $stats = StockAnalyticsService::summary();
        $byCategory = StockAnalyticsService::valuationByCategory();
        $slowMovers = StockAnalyticsService::slowMovers();
        $rotation = StockAnalyticsService::rotation();
        $replenishment = StockAnalyticsService::replenishmentList();
        $overstock = StockAnalyticsService::overstockList();
        $losses = StockAnalyticsService::inventoryLosses();
        $movementStats = StockAnalyticsService::movementStats(
            now()->subDays(30)->toDateString(),
            now()->toDateString()
        );

        return view('stock.analyse', compact(
            'stats', 'byCategory', 'slowMovers', 'rotation',
            'replenishment', 'overstock', 'losses', 'movementStats'
        ));
    }

    public function mouvements(Request $request)
    {
        $mouvements = StockMovement::with(['product', 'user'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->q, function ($q) use ($request) {
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$request->q.'%'));
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);
        $movementStats = StockAnalyticsService::movementStats($request->from, $request->to);

        return view('stock.mouvements', compact('mouvements', 'products', 'movementStats'));
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
        ]);

        try {
            $result = StockImportService::import($request->file('file'));
            ActivityLogger::log(
                'import_stock',
                'stock',
                "Import stock : {$result['updated']} mis à jour, {$result['skipped']} ignorés"
            );

            $message = "✅ Import terminé : {$result['updated']} produit(s) mis à jour.";
            if ($result['skipped'] > 0) {
                $message .= " {$result['skipped']} ligne(s) ignorée(s).";
            }

            return back()
                ->with('success', $message)
                ->with('import_errors', $result['errors']);
        } catch (\Throwable $e) {
            return back()->with('error', '❌ Import impossible : '.$e->getMessage());
        }
    }
}
