<?php

namespace AnourValar\LaravelAtom\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

class ClientRestrict
{
    protected $header = 'X-Api-Restrict';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param ... $supportedClients
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$supportedClients)
    {
        $apiRestrict = $request->headers->get($this->header);

        if ($apiRestrict && !in_array($apiRestrict, $supportedClients)) {
            throw new \AnourValar\LaravelAtom\Exceptions\UnsupportedClientException();
        }

        return $next($request);
    }
}
