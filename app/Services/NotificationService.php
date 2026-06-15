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

    public static function alertStockFaible(): void
    {
        $stocks = Stock::with('product')->whereColumn('quantity', '<=', 'min_quantity')->get();
        foreach ($stocks as $stock) {
            static::send(
                null,
                'stock_faible',
                '⚠️ Stock faible',
                "Le produit « {$stock->product->name} » est en stock critique ({$stock->quantity} restants).",
                'warning',
                ['product_id' => $stock->product_id]
            );
        }
    }

    public static function alertAdmins(string $type, string $titre, string $message, string $niveau = 'info'): void
    {
        User::whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'super_admin']))->each(function ($user) use ($type, $titre, $message, $niveau) {
            static::send($user->id, $type, $titre, $message, $niveau);
        });
    }
}
