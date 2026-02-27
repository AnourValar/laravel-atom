<?php

namespace AnourValar\LaravelAtom\Http;

trait AnalyticsTrait
{
    /**
     * Retrieve analytics
     *
     * @param array $request
     * @param int $cacheSeconds
     * @param string $configPath
     * @return array
     * @throws \RuntimeException
     */
    public function retrieveAnalytics(array $request = [], int $cacheSeconds = 15 * 60, string $configPath = 'entities.analytics.type'): array
    {
        // Identify
        $type = \Request::route('type');
        if (! $handler = config("{$configPath}.{$type}.bind")) {
            abort(404);
        }

        $handler = \App::make($handler);
        if (! $handler instanceof \AnourValar\LaravelAtom\AnalyticsInterface) {
            throw new \RuntimeException('Incorrect usage.');
        }

        // Validate
        $validator = \Validator::make(array_replace(['page' => 1, 'per_page' => 20], \Request::input(), $request), []);
        $handler->validate(\Request::user(), $validator);
        $request = $validator->stopOnFirstFailure()->validate();

        // Authorize
        $handler->authorize(\Request::user(), $request);

        // Get the response
        $response = \Cache::tags($handler->cacheTag($request))->remember(
            implode(' / ', [__METHOD__, $type, config('app.timezone_client'), sha1(\Atom::normalizeKey($request))]),
            $cacheSeconds,
            fn () => $handler->getData($request)
        );

        // ...
        return compact('handler', 'request', 'response');
    }
}
