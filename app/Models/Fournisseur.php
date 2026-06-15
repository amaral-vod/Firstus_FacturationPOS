<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    protected $fillable = [
        'site_id', 'code', 'name', 'phone', 'email', 'address',
        'ifu', 'balance', 'is_active',
    ];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(FournisseurReglement::class);
    }
}
