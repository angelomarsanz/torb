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
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'mensaje_usuario' => __('Debes iniciar sesión para continuar.'),
                    'code' => 401
                ], 401);
            }
            return redirect()->guest('login');
        }

        return $next($request);
    }
}