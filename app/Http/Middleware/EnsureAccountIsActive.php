<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs out a staff member whose account has been deactivated, on whichever
 * request comes next: a session that was already open, a "remember me"
 * cookie or a passkey sign-in.
 */
class EnsureAccountIsActive
{
    /**
     * Shown on the sign-in page to someone whose account is deactivated.
     */
    public const string MESSAGE = 'This account has been deactivated. If you think this is a mistake, please contact the CHICD office.';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isActive()) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => self::MESSAGE]);
    }
}
