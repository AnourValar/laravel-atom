<?php

namespace AnourValar\LaravelAtom\Exceptions;

class UnsupportedClientException extends \Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {

    }

    /**
     * @see \Illuminate\Foundation\Exceptions\Handler::render()
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render(\Illuminate\Http\Request $request): \Illuminate\Http\Response
    {
        return response([
            'message' => 'The client is no longer supported.',
            'errors' => [],
        ], 400);
    }
}
