<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Timezone
{
    /**
     * Config
     *
     * @var string
     */
    protected $header = 'X-Timezone';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = mb_substr((string) $request->header($this->header), 0, 100);

        if (in_array($timezone, timezone_identifiers_list(\DateTimeZone::ALL_WITH_BC))) {
            config(['atom.timezone_client' => $timezone]);
        }

        return $next($request);
    }
}
