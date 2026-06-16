<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    /**
     * @return array{orders: Collection<int, PurchaseOrder>, skipped: array<int, string>}
     */
    public static function generateFromLowStock(?int $siteId = null): array
    {
        $siteId = StockService::resolveSiteId($siteId);
        $stocks = Stock::with(['product.fournisseur'])
            ->where('site_id', $siteId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->get();

        $grouped = $stocks->groupBy(fn (Stock $stock) => $stock->product?->fournisseur_id ?? 0);
        $orders = collect();
        $skipped = [];

        return DB::transaction(function () use ($grouped, $siteId, $orders, $skipped) {
            foreach ($grouped as $fournisseurId => $items) {
                if (! $fournisseurId) {
                    foreach ($items as $stock) {
                        $skipped[] = "{$stock->product->name} — aucun fournisseur associé";
                    }

                    continue;
                }

                $order = PurchaseOrder::create([
                    'reference' => self::nextReference(),
                    'fournisseur_id' => $fournisseurId,
                    'site_id' => $siteId,
                    'user_id' => Auth::id(),
                    'status' => 'brouillon',
                    'ordered_at' => now()->toDateString(),
                    'notes' => 'Générée automatiquement depuis les alertes stock faible',
                ]);

                foreach ($items as $stock) {
                    $product = $stock->product;
                    $target = max($stock->max_quantity, $stock->min_quantity * 2, $stock->min_quantity + 1);
                    $toOrder = max(1, $target - $stock->quantity);

                    PurchaseOrderLine::create([
                        'purchase_order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity_ordered' => $toOrder,
                        'unit_cost' => (float) ($product->cost ?? 0),
                    ]);
                }

                $order->recalculateTotal();
                $orders->push($order->fresh(['fournisseur', 'site', 'lines.product']));
            }

            return ['orders' => $orders, 'skipped' => $skipped];
        });
    }

    public static function receive(PurchaseOrder $order, array $receivedQtys): PurchaseOrder
    {
        if (! $order->isEditable()) {
            throw new \RuntimeException('Cette commande ne peut plus être réceptionnée.');
        }

        return DB::transaction(function () use ($order, $receivedQtys) {
            $order->load('lines.product');
            $anyReceived = false;
            $allComplete = true;

            foreach ($order->lines as $line) {
                $qty = max(0, (int) ($receivedQtys[$line->id] ?? 0));
                if ($qty === 0) {
                    if ($line->remainingQty() > 0) {
                        $allComplete = false;
                    }

                    continue;
                }

                $qty = min($qty, $line->remainingQty());
                if ($qty === 0) {
                    continue;
                }

                StockService::adjust(
                    $line->product,
                    $qty,
                    'entree',
                    $order->reference,
                    "Réception commande {$order->reference}",
                    Auth::id(),
                    $order->site_id
                );

                $line->increment('quantity_received', $qty);
                $anyReceived = true;

                if ($line->fresh()->remainingQty() > 0) {
                    $allComplete = false;
                }
            }

            if (! $anyReceived) {
                throw new \RuntimeException('Indiquez au moins une quantité reçue.');
            }

            $order->update([
                'status' => $allComplete ? 'recue' : 'partielle',
            ]);

            return $order->fresh(['fournisseur', 'site', 'lines.product']);
        });
    }

    public static function nextReference(): string
    {
        $prefix = 'BC-'.now()->format('Ym').'-';
        $last = PurchaseOrder::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $seq = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
