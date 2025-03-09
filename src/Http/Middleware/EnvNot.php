<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnvNot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  array $envs
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$envs): Response
    {
        if (in_array(config('app.env'), $envs)) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        return $next($request);
    }
}
