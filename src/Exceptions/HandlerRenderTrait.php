<?php

namespace AnourValar\LaravelAtom\Exceptions;

use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;

trait HandlerRenderTrait
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);


        // ThrottleRequestsException
        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {

            $error = trans('laravel-atom::exception.throttle', ['seconds' => $e->getHeaders()['Retry-After']]);

            if ($request->expectsJson()) {
                $response->setData(['message' => $e->getMessage(), 'errors' => ['error' => [$error]]]);
            } else {
                return redirect()->back()->withErrors(['error' => $error]);
            }

        }

        // NotFoundHttpException, ModelNotFoundException
        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {

            if ($request->expectsJson()) {
                $response->setData(['message' => ($e->getMessage() ?: 'Not Found.'), 'errors' => []]);
            }

        }

        // InvalidSignatureException
        if ($e instanceof \Illuminate\Routing\Exceptions\InvalidSignatureException) {

            if ($request->expectsJson()) {
                $response->setData(['message' => $e->getMessage(), 'errors' => []]);
            }

        }

        // MethodNotAllowedHttpException
        if ($e instanceof MethodNotAllowedHttpException) {

            if ($request->expectsJson()) {
                $response->setData(['message' => ($e->getMessage() ?: 'Method Not Allowed.'), 'errors' => []]);
            } else {
                //abort(404);
            }

        }

        // AuthorizationException
        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {

            if ($request->expectsJson()) {
                $response->setData(['message' => 'This action is unauthorized.', 'errors' => ['error' => [trans($e->getMessage())]]]);
            }

        }

        // AuthenticationException
        if ($e instanceof \Illuminate\Auth\AuthenticationException) {

            if ($request->expectsJson()) {
                $response->setData(['message' => $e->getMessage(), 'errors' => []]);
            }

        }

        // HttpException => verified
        if ($e instanceof HttpException && $e->getMessage() == 'Your email address is not verified.') {
            if ($request->expectsJson()) {
                $response->setData(['message' => $e->getMessage(), 'errors' => []]);
            }
        }

        // TokenMismatchException
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                $response->setData(['message' => $e->getMessage(), 'errors' => []]);
            }
        }


        return $response;
    }
}