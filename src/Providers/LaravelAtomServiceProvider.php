<?php

namespace AnourValar\LaravelAtom\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LaravelAtomServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // config
        $this->mergeConfigFrom(__DIR__.'/../resources/config/atom.php', 'atom');

        $this->app->singleton(\AnourValar\LaravelAtom\Service::class, function ($app) {
            return new \AnourValar\LaravelAtom\Service(new \AnourValar\LaravelAtom\Registry());
        });

        // Exception handler
        $this->exceptionHandler();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // config
        $this->publishes([ __DIR__.'/../resources/config/atom.php' => config_path('atom.php')], 'config');

        // langs
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'laravel-atom');
        $this->publishes([__DIR__.'/../resources/lang/' => lang_path('vendor/laravel-atom')]);

        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // events
        \Event::listen([TransactionCommitted::class, TransactionRolledBack::class], function ($event) {
            \App::make(\AnourValar\LaravelAtom\Service::class)->triggerTransaction($event);
        });
    }

    /**
     * @return void
     */
    private function exceptionHandler()
    {
        $exceptionHandler = resolve(\Illuminate\Contracts\Debug\ExceptionHandler::class);

        // ThrottleRequestsException
        $exceptionHandler->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            $error = trans('laravel-atom::exception.throttle', ['seconds' => $e->getHeaders()['Retry-After']]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => ['error' => [$error]]], $e->getStatusCode());
            } else {
                //return redirect()->back()->withErrors(['error' => $error])->withInput();
            }
        });

        // NotFoundHttpException, ModelNotFoundException
        $exceptionHandler->renderable(function (NotFoundHttpException|ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                $message = $e->getMessage();
                if (stripos($message, 'No query results') !== false) {
                    $message = 'No query results.';
                }
                if (! $message) {
                    $message = 'Not Found.';
                }

                return response()->json(['message' => $message, 'errors' => []], $e->getStatusCode());
            }
        });

        // InvalidSignatureException
        $exceptionHandler->renderable(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], $e->getStatusCode());
            }
        });

        // MethodNotAllowedHttpException
        $exceptionHandler->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ($e->getMessage() ?: 'Method Not Allowed.'), 'errors' => []], $e->getStatusCode());
            }
        });

        // AuthorizationException
        $exceptionHandler->renderable(function (AuthorizationException|AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => ['error' => [trans($e->getMessage())]]], $e->getStatusCode());
            }
        });

        // AuthenticationException
        $exceptionHandler->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], 401);
            }
        });

        // HttpException [verified]
        $exceptionHandler->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson() && $e->getMessage() == 'Your email address is not verified.') {
                return response()->json(['message' => $e->getMessage(), 'errors' => ['error' => [trans($e->getMessage())]]], $e->getStatusCode());
            }
        });

        // TokenMismatchException, HttpException [token mismatch]
        $exceptionHandler->renderable(function (TokenMismatchException|HttpException $e, $request) {
            if ($request->expectsJson() && stripos($e->getMessage(), 'token mismatch') !== false) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], $e->getStatusCode());
            }
        });

        // JsonEncodingException [on json columns], InvalidArgumentException [on json content-type], QueryException [invalid encoding]
        $exceptionHandler->renderable(function (JsonEncodingException|\InvalidArgumentException|QueryException $e, $request) {
            if (
                $e instanceof JsonEncodingException
                || ($e instanceof \InvalidArgumentException && stripos($e->getMessage(), 'Malformed UTF-8 characters') !== false)
                || ($e instanceof QueryException && stripos($e->getMessage(), 'invalid byte sequence for encoding "UTF8"') !== false)
            ) {
                if ($request->expectsJson()) {
                    return response(['message' => 'Malformed UTF-8 characters, possibly incorrectly encoded.', 'errors' => []], 400);
                } else {
                    return response('Malformed UTF-8 characters, possibly incorrectly encoded.', 400);
                }
            }
        });

        // MassAssignmentException
        $exceptionHandler->renderable(function (\Illuminate\Database\Eloquent\MassAssignmentException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => []], 400);
            } else {
                return response($e->getMessage(), 400);
            }
        });
    }
}
