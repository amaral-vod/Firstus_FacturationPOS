<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'email', 'ip_address', 'user_agent',
        'success', 'logged_in_at', 'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
