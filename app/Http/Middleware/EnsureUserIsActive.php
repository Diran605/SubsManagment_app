<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs out users whose account has been deactivated.
 *
 * The login check in FortifyServiceProvider only covers password logins, so
 * this also catches passkey logins and sessions that were already open when
 * the account was deactivated.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_active === false) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => __('Your account has been deactivated.'),
            ]);
        }

        return $next($request);
    }
}
