<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return redirect()->route('login')->with('error', '🔒 Accès non autorisé.');
        }

        if (! $user->hasPermission($permission)) {
            abort(403, '⛔ Vous n\'avez pas la permission d\'accéder à cette page.');
        }

        return $next($request);
    }
}
