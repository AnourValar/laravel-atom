<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  array $ips
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$ips): Response
    {
        if (! \Symfony\Component\HttpFoundation\IpUtils::checkIp($request->ip(), $ips)) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
