<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'site_id', 'code', 'name', 'phone', 'email', 'address',
        'ifu', 'credit_limit', 'balance_due', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(ClientCredit::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
