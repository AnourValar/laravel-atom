<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigNot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  array $options
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$options): Response
    {
        if (! isset($options[1])) {
            throw new \RuntimeException('Incorrect usage.');
        }

        if (config($options[0]) == $options[1]) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        return $next($request);
    }
}
