<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BriefAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $brief = $request->route('brief');
        if (
            $brief
            && $brief->user_id !== auth()->id()
            && !auth()->user()->isAdmin()
        ) {
            return redirect()->route('briefs.index')
                ->with('error', 'Бриф не найден или не принадлежит данному пользователю.');
        }
        return $next($request);
    }
}
