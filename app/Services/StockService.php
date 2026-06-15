<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public static function adjust(Product $product, int $quantity, string $type, ?string $reference = null, ?string $notes = null, ?int $userId = null): Stock
    {
        return DB::transaction(function () use ($product, $quantity, $type, $reference, $notes, $userId) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id],
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

    public static function lowStockProducts()
    {
        return Stock::with('product')
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->get();
    }
}
