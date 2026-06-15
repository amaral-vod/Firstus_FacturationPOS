<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annulation extends Model
{
    protected $fillable = ['vente_id', 'user_id', 'motif', 'annulee_le'];

    protected function casts(): array
    {
        return ['annulee_le' => 'datetime'];
    }

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
