<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['product_id', 'site_id', 'quantity', 'min_quantity', 'max_quantity', 'location'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isLow(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }

    public function isOverstock(): bool
    {
        return $this->max_quantity > 0 && $this->quantity > $this->max_quantity;
    }

    public function valuation(): float
    {
        return $this->quantity * (float) ($this->product?->cost ?? 0);
    }
}
