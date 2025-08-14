<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если роль не admin — запрет
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Только для администраторов');
        }

        return $next($request);
    }
}
