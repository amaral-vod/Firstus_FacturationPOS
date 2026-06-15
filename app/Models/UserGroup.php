<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserGroup extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_user');
    }
}
