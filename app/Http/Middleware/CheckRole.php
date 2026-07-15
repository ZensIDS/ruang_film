<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $roles = array_slice(func_get_args(), 2);
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole($roles)) {
            return $next($request);
        }

        return redirect()->route('dashboard')
            ->with('warning', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
