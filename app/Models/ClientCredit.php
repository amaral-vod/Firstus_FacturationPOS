<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCredit extends Model
{
    protected $fillable = [
        'client_id', 'vente_id', 'facture_id', 'montant', 'montant_paye',
        'date_echeance', 'statut', 'relance_le', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'montant_paye' => 'decimal:2',
            'date_echeance' => 'date',
            'relance_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reste(): float
    {
        return (float) $this->montant - (float) $this->montant_paye;
    }
}
