<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationIsOpen
{
    /**
     * Registration is open until the first account exists. After that the
     * instance is claimed and signups close, unless the operator opts back
     * in with PDFPOST_ALLOW_REGISTRATION=true.
     */
    public static function open(): bool
    {
        return config('pdfpost.allow_registration') || User::doesntExist();
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! static::open()) {
            abort(403, 'Registration is closed on this PDFPost instance.');
        }

        return $next($request);
    }
}
