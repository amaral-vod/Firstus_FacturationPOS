<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FournisseurReglement extends Model
{
    protected $fillable = [
        'fournisseur_id', 'user_id', 'montant', 'date_echeance',
        'mode_paiement', 'notes',
    ];

    protected function casts(): array
    {
        return ['montant' => 'decimal:2', 'date_echeance' => 'date'];
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
