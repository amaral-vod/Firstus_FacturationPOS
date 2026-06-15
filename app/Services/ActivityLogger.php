<?php

namespace App\Services;

use App\Models\JournalActivite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $module, string $description, ?array $metadata = null): void
    {
        JournalActivite::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
        ]);
    }
}
