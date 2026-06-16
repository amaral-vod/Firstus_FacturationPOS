<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Site;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public static function resolveSiteId(?int $siteId = null): int
    {
        if ($siteId) {
            return $siteId;
        }

        $userSiteId = Auth::user()?->site_id;
        if ($userSiteId) {
            return (int) $userSiteId;
        }

        return InventoryService::defaultSiteId();
    }

    public static function adjust(
        Product $product,
        int $quantity,
        string $type,
        ?string $reference = null,
        ?string $notes = null,
        ?int $userId = null,
        ?int $siteId = null,
    ): Stock {
        $siteId = self::resolveSiteId($siteId);

        return DB::transaction(function () use ($product, $quantity, $type, $reference, $notes, $userId, $siteId) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'site_id' => $siteId],
                ['quantity' => 0, 'min_quantity' => 5]
            );

            $before = $stock->quantity;
            $after = match ($type) {
                'entree', 'retour', 'annulation' => $before + $quantity,
                'sortie' => $before - $quantity,
                'inventaire' => $quantity,
                default => $before,
            };

            if ($type === 'sortie' && $after < 0) {
                throw new \RuntimeException("Stock insuffisant pour {$product->name}");
            }

            $stock->update(['quantity' => $after]);

            StockMovement::create([
                'product_id' => $product->id,
                'site_id' => $siteId,
                'user_id' => $userId ?? Auth::id(),
                'type' => $type,
                'quantity' => abs($quantity),
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            return $stock->fresh();
        });
    }

    public static function lowStockProducts(?int $siteId = null)
    {
        $siteId = self::resolveSiteId($siteId);

        return Stock::with('product')
            ->where('site_id', $siteId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->get();
    }

    public static function activeSites()
    {
        return Site::where('is_active', true)->orderBy('name')->get();
    }
}
