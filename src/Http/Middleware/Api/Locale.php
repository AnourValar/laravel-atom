<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    protected $header = 'Accept-Language';

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
        $locale = mb_substr((string) $request->header($this->header), 0, 2);
        $locale = mb_strtolower($locale);

        if (in_array($locale, $supportedLocales)) {
            \App::setLocale($locale);
        }

        return $next($request);
    }
}
