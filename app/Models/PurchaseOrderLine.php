<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id', 'product_id',
        'quantity_ordered', 'quantity_received', 'unit_cost',
    ];

    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): float
    {
        return $this->quantity_ordered * (float) $this->unit_cost;
    }

    public function remainingQty(): int
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }
}
