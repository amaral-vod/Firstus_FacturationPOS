<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public static function send(?int $userId, string $type, string $titre, string $message, string $niveau = 'info', ?array $metadata = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'niveau' => $niveau,
            'metadata' => $metadata,
        ]);
    }

    public static function alertStockFaible(): int
    {
        $count = 0;
        $stocks = Stock::with('product')->whereColumn('quantity', '<=', 'min_quantity')->get();

        foreach ($stocks as $stock) {
            if (static::alreadyNotifiedToday('stock_faible', $stock->product_id)) {
                continue;
            }

            static::alertAdmins(
                'stock_faible',
                '⚠️ Stock faible',
                "« {$stock->product->name} » : {$stock->quantity} restant(s) (seuil {$stock->min_quantity}).",
                'warning',
                ['product_id' => $stock->product_id]
            );
            $count++;
        }

        return $count;
    }

    public static function alertSurstock(): int
    {
        $count = 0;
        $stocks = Stock::with('product')
            ->where('max_quantity', '>', 0)
            ->whereColumn('quantity', '>', 'max_quantity')
            ->get();

        foreach ($stocks as $stock) {
            if (static::alreadyNotifiedToday('stock_surstock', $stock->product_id)) {
                continue;
            }

            static::alertAdmins(
                'stock_surstock',
                '📦 Surstock',
                "« {$stock->product->name} » : {$stock->quantity} unités (max {$stock->max_quantity}).",
                'info',
                ['product_id' => $stock->product_id]
            );
            $count++;
        }

        return $count;
    }

    private static function alreadyNotifiedToday(string $type, int $productId): bool
    {
        return Notification::where('type', $type)
            ->whereDate('created_at', today())
            ->whereJsonContains('metadata->product_id', $productId)
            ->exists();
    }

    public static function alertAdmins(string $type, string $titre, string $message, string $niveau = 'info', ?array $metadata = null): void
    {
        User::whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'super_admin', 'magasinier']))
            ->each(function ($user) use ($type, $titre, $message, $niveau, $metadata) {
                static::send($user->id, $type, $titre, $message, $niveau, $metadata);
            });
    }
}
