<?php

namespace AnourValar\LaravelAtom\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests as BaseMiddleware;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;
use Illuminate\Routing\ControllerDispatcher;

class HandlePrecognitiveRequests extends BaseMiddleware
{
    /**
     * Prepare to handle a precognitive request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function prepareForPrecognition($request)
    {
        parent::prepareForPrecognition($request); // @TODO: check with a docs

        if ($request->isPrecognitive()) {
            $request->headers->set('Accept', 'application/json');
        }

        $this->container->bind(CallableDispatcherContract::class, fn ($app) => new CallableDispatcher($app));
        $this->container->bind(ControllerDispatcherContract::class, fn ($app) => new ControllerDispatcher($app));
    }
}
