<?php

namespace Reda\RedaAlojamiento\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPluginAuth
{
    public function handle($request, Closure $next)
    {       
        $isAuthenticated = Auth::check() || auth()->user() || Auth::guard('web')->check();

        if (!$isAuthenticated) {
            return redirect()->guest('login');
        }

        return $next($request);
    }
}