<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($this->resolveLocale($request));

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $supported = config('supported_locales.supported');
        $userLocale = $request->user()?->locale;

        if (is_string($userLocale) && in_array($userLocale, $supported, true)) {
            return $userLocale;
        }

        return $request->getPreferredLanguage($supported) ?? config('supported_locales.default');
    }
}
