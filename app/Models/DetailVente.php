<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailVente extends Model
{
    protected $fillable = [
        'vente_id', 'product_id', 'quantite', 'quantite_retournee',
        'prix_unitaire', 'total_ligne',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
            'total_ligne' => 'decimal:2',
        ];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quantiteRestante(): int
    {
        return $this->quantite - $this->quantite_retournee;
    }
}
