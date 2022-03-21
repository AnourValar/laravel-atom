<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Env
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  array $envs
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$envs)
    {
        if (! in_array(config('app.env'), $envs)) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        return $next($request);
    }
}
