<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use App\Services\PurchaseOrderService;
use App\Services\StockAnalyticsService;
use App\Services\StockImportService;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $siteId = StockService::resolveSiteId($request->integer('site_id') ?: null);
        $sites = StockService::activeSites();

        $stocks = Stock::with(['product.category', 'site'])
            ->where('site_id', $siteId)
            ->when($request->alerte, fn ($q) => $q->whereColumn('quantity', '<=', 'min_quantity'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $lowCount = Stock::where('site_id', $siteId)->whereColumn('quantity', '<=', 'min_quantity')->count();
        $stats = StockAnalyticsService::summary($siteId);

        return view('stock.index', compact('stocks', 'products', 'lowCount', 'stats', 'sites', 'siteId'));
    }

    public function analyse(Request $request)
    {
        $siteId = StockService::resolveSiteId($request->integer('site_id') ?: null);
        $sites = StockService::activeSites();

        $stats = StockAnalyticsService::summary($siteId);
        $byCategory = StockAnalyticsService::valuationByCategory($siteId);
        $slowMovers = StockAnalyticsService::slowMovers(siteId: $siteId);
        $rotation = StockAnalyticsService::rotation(siteId: $siteId);
        $replenishment = StockAnalyticsService::replenishmentList($siteId);
        $overstock = StockAnalyticsService::overstockList($siteId);
        $losses = StockAnalyticsService::inventoryLosses();
        $movementStats = StockAnalyticsService::movementStats(
            now()->subDays(30)->toDateString(),
            now()->toDateString(),
            $siteId
        );

        return view('stock.analyse', compact(
            'stats', 'byCategory', 'slowMovers', 'rotation',
            'replenishment', 'overstock', 'losses', 'movementStats', 'sites', 'siteId'
        ));
    }

    public function mouvements(Request $request)
    {
        $siteId = StockService::resolveSiteId($request->integer('site_id') ?: null);
        $sites = StockService::activeSites();

        $mouvements = StockMovement::with(['product', 'user', 'site'])
            ->where('site_id', $siteId)
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
        $movementStats = StockAnalyticsService::movementStats($request->from, $request->to, $siteId);

        return view('stock.mouvements', compact('mouvements', 'products', 'movementStats', 'sites', 'siteId'));
    }

    public function entree(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $product = Product::findOrFail($data['product_id']);
        StockService::adjust(
            $product,
            $data['quantity'],
            'entree',
            null,
            $data['notes'] ?? 'Entrée de stock',
            null,
            $data['site_id'] ?? null
        );
        ActivityLogger::log('entree_stock', 'stock', "Entrée +{$data['quantity']} pour {$product->name}");

        return back()->with('success', '📦 Entrée de stock enregistrée.');
    }

    public function sortie(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        try {
            $product = Product::findOrFail($data['product_id']);
            StockService::adjust(
                $product,
                $data['quantity'],
                'sortie',
                null,
                $data['notes'] ?? 'Sortie de stock',
                null,
                $data['site_id'] ?? null
            );
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
            'site_id' => 'nullable|exists:sites,id',
        ]);

        $product = Product::findOrFail($data['product_id']);
        StockService::adjust(
            $product,
            $data['quantity'],
            'inventaire',
            null,
            $data['notes'] ?? 'Inventaire',
            null,
            $data['site_id'] ?? null
        );
        ActivityLogger::log('inventaire', 'stock', "Inventaire {$product->name} => {$data['quantity']}");

        return back()->with('success', '📋 Inventaire mis à jour.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        try {
            $result = StockImportService::import(
                $request->file('file'),
                $request->integer('site_id') ?: null
            );
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
