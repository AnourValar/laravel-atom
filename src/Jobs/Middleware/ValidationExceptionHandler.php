<?php

namespace AnourValar\LaravelAtom\Jobs\Middleware;

class ValidationExceptionHandler
{
    /**
     * Process the queued job.
     *
     * @param  mixed  $job
     * @param  callable  $next
     * @return mixed
     */
    public function handle($job, $next)
    {
        try {
            $next($job);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $job->fail(); // no retry

            throw \AnourValar\LaravelAtom\Exceptions\InternalValidationException::fromValidationException($e);
        }
    }
}
