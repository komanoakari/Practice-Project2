<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class AutoLogoutIfUnverifiedOnLogin
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('get') && $request->is('login') && auth()->check()) {
            $user = $request->user();

            if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                auth()->logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->guest(route('login'));
            }
        }

        return $next($request);
    }
}