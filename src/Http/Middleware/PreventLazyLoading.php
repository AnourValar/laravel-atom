<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventLazyLoading
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());

        return $next($request);
    }
}
