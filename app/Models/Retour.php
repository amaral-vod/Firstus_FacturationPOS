<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Retour extends Model
{
    protected $fillable = [
        'numero_retour', 'vente_id', 'user_id', 'type', 'motif', 'montant_rembourse',
    ];

    protected function casts(): array
    {
        return ['montant_rembourse' => 'decimal:2'];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(RetourDetail::class);
    }
}
