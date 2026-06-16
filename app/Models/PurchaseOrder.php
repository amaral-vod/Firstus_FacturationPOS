<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUSES = [
        'brouillon' => 'Brouillon',
        'envoyee' => 'Envoyée',
        'partielle' => 'Réception partielle',
        'recue' => 'Reçue',
        'annulee' => 'Annulée',
    ];

    protected $fillable = [
        'reference', 'fournisseur_id', 'site_id', 'user_id',
        'status', 'ordered_at', 'notes', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['brouillon', 'envoyee', 'partielle'], true);
    }

    public function recalculateTotal(): void
    {
        $total = $this->lines()->get()->sum(
            fn (PurchaseOrderLine $line) => $line->quantity_ordered * (float) $line->unit_cost
        );
        $this->update(['total_amount' => round($total, 2)]);
    }
}
