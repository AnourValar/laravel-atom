<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Token
{
    protected $header = 'Authorization';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param string $tokenConfigHapth
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $tokenConfigHapth): Response
    {
        $token = (string) $request->headers->get($this->header);

        if (mb_strlen($token) < 6 || ! in_array($token, (array) config($tokenConfigHapth), true)) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
