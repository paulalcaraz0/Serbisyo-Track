<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var array<string, string> $locales */
        $locales = config('serbisyo.locales');
        $locale = $request->session()->get('locale', config('app.locale'));

        if (is_string($locale) && array_key_exists($locale, $locales)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
