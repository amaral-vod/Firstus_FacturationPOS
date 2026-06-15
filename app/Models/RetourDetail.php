<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetourDetail extends Model
{
    protected $fillable = [
        'retour_id', 'product_id', 'detail_vente_id',
        'quantite', 'prix_unitaire', 'total_ligne',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'decimal:2',
            'total_ligne' => 'decimal:2',
        ];
    }

    public function retour(): BelongsTo
    {
        return $this->belongsTo(Retour::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function detailVente(): BelongsTo
    {
        return $this->belongsTo(DetailVente::class);
    }
}
