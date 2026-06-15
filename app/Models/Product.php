<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'sku', 'barcode', 'description',
        'price', 'cost', 'promo_price', 'promo_start', 'promo_end', 'unit', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'promo_start' => 'datetime',
            'promo_end' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->promo_price && $this->isPromoActive()) {
            return (float) $this->promo_price;
        }

        return (float) $this->price;
    }

    public function isPromoActive(): bool
    {
        if (! $this->promo_price) {
            return false;
        }

        $now = now();

        if ($this->promo_start && $now->lt($this->promo_start)) {
            return false;
        }

        if ($this->promo_end && $now->gt($this->promo_end)) {
            return false;
        }

        return true;
    }
}
