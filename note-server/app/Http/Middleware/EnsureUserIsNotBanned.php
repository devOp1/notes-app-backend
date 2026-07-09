<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsNotBanned
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_banned) {
            abort(403, 'Dieses Konto wurde gesperrt.');
        }

        return $next($request);
    }
}
