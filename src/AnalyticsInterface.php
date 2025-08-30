<?php

namespace AnourValar\LaravelAtom;

/**
 * Controller example:
 *
 * $type = $request->route('type');
 * if (! config("entities.analytics.type.{$type}")) {
 *     abort(404);
 * }
 * $handler = \App::make(config("entities.analytics.type.{$type}.bind"));
 *
 * $validator = \Validator::make($request->input(), []);
 * $handler->validate($request->user(), $validator);
 * $data = $validator->validate();
 *
 * $handler->authorize($request->user(), $data);
 *
 * return \Cache::tags($handler->cacheTag($request->user(), $data))->remember(
 *     implode(' / ', [__METHOD__, $type, $request->user()->id, config('app.timezone_client'), sha1(json_encode($data))]),
 *     15 * 60, // 15 minutes
 *     fn () => $handler->getData($request->user(), $data)
 * );
 *
 */

interface AnalyticsInterface
{
    /**
     * Request validation
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function validate(?\Illuminate\Foundation\Auth\User $user, \Illuminate\Validation\Validator &$validator): void;

    /**
     * Request authorization
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @param array $data
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize(?\Illuminate\Foundation\Auth\User $user, array $data): void;

    /**
     * Cache tag
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @param array $data
     * @return string|null
     */
    public function cacheTag(?\Illuminate\Foundation\Auth\User $user, array $data): ?string; // 'user:' . $user->id

    /**
     * Analytics data
     *
     * @param \Illuminate\Foundation\Auth\User|null $user
     * @param array $data
     * @return array
     */
    public function getData(?\Illuminate\Foundation\Auth\User $user, array $data): array;
}
