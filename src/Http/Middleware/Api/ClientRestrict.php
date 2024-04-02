<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientRestrict
{
    protected $header = 'X-Api-Restrict';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param ... $supportedClients
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$supportedClients): Response
    {
        $apiRestrict = $request->headers->get($this->header);

        if ($apiRestrict && ! in_array($apiRestrict, $supportedClients)) {
            throw new \AnourValar\LaravelAtom\Exceptions\UnsupportedClientException();
        }

        return $next($request);
    }
}
