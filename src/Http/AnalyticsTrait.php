<?php

namespace AnourValar\LaravelAtom\Http;

trait AnalyticsTrait
{
    /**
     * Retrieve analytics
     *
     * @param int $cacheMinutes
     * @param string $configPath
     * @return array
     * @throws \RuntimeException
     */
    public function retrieveAnalytics(int $cacheMinutes = 15, string $configPath = 'entities.analytics.type'): array
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
        $validator = \Validator::make(\Request::input(), []);
        $handler->validate(\Request::user(), $validator);
        $request = $validator->stopOnFirstFailure()->validate();

        // Authorize
        $handler->authorize(\Request::user(), $request);

        // Get the response
        $response = \Cache::tags($handler->cacheTag($request))->remember(
            implode(' / ', [__METHOD__, $type, config('app.timezone_client'), sha1(json_encode($request))]),
            $cacheMinutes * 60,
            fn () => $handler->getData($request)
        );

        // ...
        return compact('handler', 'request', 'response');
    }
}
