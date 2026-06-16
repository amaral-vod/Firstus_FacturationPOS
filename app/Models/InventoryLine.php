<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    protected $fillable = [
        'inventory_session_id', 'product_id', 'theoretical_qty', 'counted_qty',
        'variance_qty', 'unit_cost', 'variance_value', 'variance_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'variance_value' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class, 'inventory_session_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reasonLabel(): ?string
    {
        return $this->variance_reason
            ? (InventorySession::REASONS[$this->variance_reason] ?? $this->variance_reason)
            : null;
    }
}
