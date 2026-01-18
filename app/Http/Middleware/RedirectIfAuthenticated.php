<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure, Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role = null)
    {
        if (Auth::guard($role)->check()) {
            if ($role == 'users') {
                if ($request->is('reda*')) {
                    return $next($request);
                }
        
                return redirect('dashboard');
            } elseif ($role == 'admin') {
                return redirect('admin/dashboard');
            }
        }
        return $next($request);
    }
}
