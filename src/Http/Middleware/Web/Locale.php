<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Web;

use Closure;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param string $configPath
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $configPath = 'models.user.locale'): Response
    {
        $locale = $request->input('locale');
        if (! is_string($locale)) {
            $locale = '';
        }

        if (isset(config($configPath)[$locale])) {

            // Set locale from request
            \App::setLocale($locale);
            $request->session()->put('locale', $locale);

        } elseif (isset(config($configPath)[$request->session()->get('locale', '')])) {

            // Set locale from session
            \App::setLocale($request->session()->get('locale'));

        } elseif ($request->user() instanceof HasLocalePreference && $locale = $request->user()->preferredLocale()) {

            // Set locale from user preference
            \App::setLocale($locale);

        } else {

            // Set locale by default
            \App::setLocale(config('app.fallback_locale'));

        }

        return $next($request);
    }
}
