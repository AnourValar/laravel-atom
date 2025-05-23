<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Web;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param ...$supportedLocales
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$supportedLocales): Response
    {
        $locale = $request->input('locale');
        if (is_string($locale)) {
            $locale = mb_strtolower($locale);
        }

        if (in_array($locale, $supportedLocales)) {
            \App::setLocale($locale);
        }

        return $next($request);
    }
}
