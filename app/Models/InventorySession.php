<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySession extends Model
{
    public const REASONS = [
        'erreur_saisie' => 'Erreur de saisie',
        'casse' => 'Produit endommagé / casse',
        'vol' => 'Vol',
        'perte' => 'Perte',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'reference', 'site_id', 'user_id', 'validated_by', 'inventory_date',
        'status', 'notes', 'total_theoretical_qty', 'total_counted_qty',
        'total_variance_qty', 'total_theoretical_value', 'total_counted_value',
        'total_variance_value', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'inventory_date' => 'date',
            'validated_at' => 'datetime',
            'total_theoretical_value' => 'decimal:2',
            'total_counted_value' => 'decimal:2',
            'total_variance_value' => 'decimal:2',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }

    public function isEditable(): bool
    {
        return $this->status === 'brouillon';
    }

    public function linesWithVariance(): HasMany
    {
        return $this->lines()->where('variance_qty', '!=', 0);
    }
}
