<?php

namespace App\Services;

use App\Models\InventoryLine;
use App\Models\InventorySession;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockAnalyticsService
{
    public static function summary(?int $siteId = null): array
    {
        $siteId = StockService::resolveSiteId($siteId);
        $stocks = Stock::with('product')->where('site_id', $siteId)->get();

        $totalQty = $stocks->sum('quantity');
        $totalValue = $stocks->sum(fn (Stock $s) => $s->valuation());
        $lowCount = $stocks->filter(fn (Stock $s) => $s->isLow())->count();
        $overCount = $stocks->filter(fn (Stock $s) => $s->isOverstock())->count();

        return [
            'total_qty' => $totalQty,
            'total_value' => round($totalValue, 2),
            'product_count' => $stocks->count(),
            'low_count' => $lowCount,
            'over_count' => $overCount,
            'zero_count' => $stocks->where('quantity', 0)->count(),
            'site_id' => $siteId,
        ];
    }

    public static function valuationByCategory(?int $siteId = null): Collection
    {
        $siteId = StockService::resolveSiteId($siteId);

        return Stock::query()
            ->where('stocks.site_id', $siteId)
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                DB::raw("COALESCE(categories.name, 'Sans catégorie') as category"),
                DB::raw('SUM(stocks.quantity) as total_qty'),
                DB::raw('SUM(stocks.quantity * COALESCE(products.cost, 0)) as total_value')
            )
            ->groupBy('category')
            ->orderByDesc('total_value')
            ->get();
    }

    /** @return Collection<int, object> */
    public static function slowMovers(int $days = 90, int $limit = 15, ?int $siteId = null): Collection
    {
        $since = now()->subDays($days);
        $siteId = StockService::resolveSiteId($siteId);

        return DB::table('stocks')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->leftJoin('detail_ventes', 'products.id', '=', 'detail_ventes.product_id')
            ->leftJoin('ventes', function ($join) use ($since) {
                $join->on('detail_ventes.vente_id', '=', 'ventes.id')
                    ->where('ventes.statut', 'complete')
                    ->where('ventes.created_at', '>=', $since);
            })
            ->where('stocks.site_id', $siteId)
            ->where('products.is_active', true)
            ->where('stocks.quantity', '>', 0)
            ->groupBy('products.id', 'products.name', 'products.sku', 'stocks.quantity', 'products.cost')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'stocks.quantity',
                'products.cost',
                DB::raw('COALESCE(SUM(detail_ventes.quantite), 0) as sold_qty'),
                DB::raw('stocks.quantity * COALESCE(products.cost, 0) as immobilized_value')
            )
            ->orderBy('sold_qty')
            ->orderByDesc('immobilized_value')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, object> */
    public static function rotation(int $days = 30, int $limit = 15, ?int $siteId = null): Collection
    {
        $since = now()->subDays($days);
        $siteId = StockService::resolveSiteId($siteId);

        return DB::table('products')
            ->join('stocks', 'products.id', '=', 'stocks.product_id')
            ->leftJoin('detail_ventes', 'products.id', '=', 'detail_ventes.product_id')
            ->leftJoin('ventes', function ($join) use ($since) {
                $join->on('detail_ventes.vente_id', '=', 'ventes.id')
                    ->where('ventes.statut', 'complete')
                    ->where('ventes.created_at', '>=', $since);
            })
            ->where('stocks.site_id', $siteId)
            ->where('products.is_active', true)
            ->groupBy('products.id', 'products.name', 'stocks.quantity')
            ->select(
                'products.id',
                'products.name',
                'stocks.quantity',
                DB::raw('COALESCE(SUM(detail_ventes.quantite), 0) as sold_qty'),
                DB::raw('CASE WHEN COALESCE(SUM(detail_ventes.quantite), 0) > 0
                    THEN ROUND(stocks.quantity / (COALESCE(SUM(detail_ventes.quantite), 0) / '.$days.'), 1)
                    ELSE NULL END as coverage_days')
            )
            ->having('sold_qty', '>', 0)
            ->orderByDesc('sold_qty')
            ->limit($limit)
            ->get();
    }

    public static function movementStats(?string $from = null, ?string $to = null, ?int $siteId = null): array
    {
        $siteId = StockService::resolveSiteId($siteId);

        $query = StockMovement::query()
            ->where('site_id', $siteId)
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $byType = (clone $query)
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('type')
            ->pluck('total_qty', 'type');

        return [
            'entrees' => (int) ($byType['entree'] ?? 0) + (int) ($byType['retour'] ?? 0) + (int) ($byType['annulation'] ?? 0),
            'sorties' => (int) ($byType['sortie'] ?? 0),
            'inventaires' => (int) ($byType['inventaire'] ?? 0),
            'total_movements' => (clone $query)->count(),
        ];
    }

    public static function inventoryLosses(int $limit = 20): Collection
    {
        return InventoryLine::query()
            ->where('variance_qty', '<', 0)
            ->whereHas('session', fn ($q) => $q->where('status', 'valide'))
            ->with(['product', 'session'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public static function replenishmentList(?int $siteId = null): Collection
    {
        $siteId = StockService::resolveSiteId($siteId);

        return Stock::with('product.category', 'product.fournisseur')
            ->where('site_id', $siteId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->orderBy('quantity')
            ->get();
    }

    public static function overstockList(?int $siteId = null): Collection
    {
        $siteId = StockService::resolveSiteId($siteId);

        return Stock::with('product.category')
            ->where('site_id', $siteId)
            ->where('max_quantity', '>', 0)
            ->whereColumn('quantity', '>', 'max_quantity')
            ->orderByDesc('quantity')
            ->get();
    }
}
