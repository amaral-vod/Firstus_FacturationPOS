<?php

namespace App\Services;

use App\Models\InventoryLine;
use App\Models\InventorySession;
use App\Models\Product;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public static function createSession(int $siteId, string $inventoryDate, ?string $notes = null): InventorySession
    {
        return DB::transaction(function () use ($siteId, $inventoryDate, $notes) {
            $session = InventorySession::create([
                'reference' => self::nextReference(),
                'site_id' => $siteId,
                'user_id' => Auth::id(),
                'inventory_date' => $inventoryDate,
                'status' => 'brouillon',
                'notes' => $notes,
            ]);

            $products = Product::with('stock')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            foreach ($products as $product) {
                $theoretical = (int) ($product->stock?->quantity ?? 0);
                $cost = (float) ($product->cost ?? 0);

                InventoryLine::create([
                    'inventory_session_id' => $session->id,
                    'product_id' => $product->id,
                    'theoretical_qty' => $theoretical,
                    'counted_qty' => $theoretical,
                    'variance_qty' => 0,
                    'unit_cost' => $cost,
                    'variance_value' => 0,
                ]);
            }

            self::recalculateTotals($session);

            return $session->fresh(['lines.product', 'site', 'user']);
        });
    }

    /**
     * @param  array<int, array{counted_qty?:int, variance_reason?:?string, notes?:?string}>  $linesData
     */
    public static function updateLines(InventorySession $session, array $linesData): InventorySession
    {
        if (! $session->isEditable()) {
            throw new \RuntimeException('Cet inventaire ne peut plus être modifié.');
        }

        return DB::transaction(function () use ($session, $linesData) {
            foreach ($linesData as $productId => $data) {
                $line = $session->lines()->where('product_id', $productId)->first();
                if (! $line) {
                    continue;
                }

                $counted = max(0, (int) ($data['counted_qty'] ?? $line->theoretical_qty));
                $variance = $counted - $line->theoretical_qty;
                $reason = $data['variance_reason'] ?? null;

                if ($variance !== 0 && empty($reason)) {
                    throw new \RuntimeException(
                        "Motif d'écart requis pour le produit #{$productId}."
                    );
                }

                $line->update([
                    'counted_qty' => $counted,
                    'variance_qty' => $variance,
                    'variance_value' => round($variance * (float) $line->unit_cost, 2),
                    'variance_reason' => $variance !== 0 ? $reason : null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            self::recalculateTotals($session);

            return $session->fresh(['lines.product', 'site', 'user']);
        });
    }

    public static function validateSession(InventorySession $session, int $validatorId): InventorySession
    {
        if (! $session->isEditable()) {
            throw new \RuntimeException('Cet inventaire est déjà clôturé.');
        }

        return DB::transaction(function () use ($session, $validatorId) {
            $session->load('lines.product');

            foreach ($session->lines as $line) {
                if ($line->variance_qty !== 0 && empty($line->variance_reason)) {
                    throw new \RuntimeException(
                        "Motif manquant pour « {$line->product->name} »."
                    );
                }

                if ($line->variance_qty !== 0) {
                    $motif = InventorySession::REASONS[$line->variance_reason] ?? $line->variance_reason;
                    StockService::adjust(
                        $line->product,
                        $line->counted_qty,
                        'inventaire',
                        $session->reference,
                        "Inventaire {$session->reference} — {$motif}".($line->notes ? " — {$line->notes}" : '')
                    );
                }
            }

            $session->update([
                'status' => 'valide',
                'validated_by' => $validatorId,
                'validated_at' => now(),
            ]);

            return $session->fresh(['lines.product', 'site', 'user', 'validator']);
        });
    }

    public static function cancelSession(InventorySession $session): InventorySession
    {
        if (! $session->isEditable()) {
            throw new \RuntimeException('Cet inventaire ne peut plus être annulé.');
        }

        $session->update(['status' => 'annule']);

        return $session;
    }

    public static function recalculateTotals(InventorySession $session): void
    {
        $lines = $session->lines()->get();

        $session->update([
            'total_theoretical_qty' => $lines->sum('theoretical_qty'),
            'total_counted_qty' => $lines->sum('counted_qty'),
            'total_variance_qty' => $lines->sum('variance_qty'),
            'total_theoretical_value' => $lines->sum(fn ($l) => $l->theoretical_qty * $l->unit_cost),
            'total_counted_value' => $lines->sum(fn ($l) => $l->counted_qty * $l->unit_cost),
            'total_variance_value' => $lines->sum('variance_value'),
        ]);
    }

    public static function nextReference(): string
    {
        $prefix = 'INV-'.now()->format('Ymd');
        $last = InventorySession::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    public static function defaultSiteId(): int
    {
        return Site::where('is_default', true)->value('id')
            ?? Site::where('is_active', true)->value('id')
            ?? Site::query()->value('id');
    }
}
