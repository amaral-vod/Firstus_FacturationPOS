<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaisseSession extends Model
{
    protected $fillable = [
        'user_id', 'site_id', 'opened_at', 'closed_at',
        'fond_initial', 'fond_theorique', 'fond_reel', 'ecart',
        'ecart_type', 'statut', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'fond_initial' => 'decimal:2',
            'fond_theorique' => 'decimal:2',
            'fond_reel' => 'decimal:2',
            'ecart' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public static function sessionOuverte(?int $userId = null): ?self
    {
        return static::where('statut', 'ouverte')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest('opened_at')
            ->first();
    }
}
