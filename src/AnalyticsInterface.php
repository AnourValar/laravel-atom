<?php

namespace AnourValar\LaravelAtom;

interface AnalyticsInterface // @see \AnourValar\LaravelAtom\Http\AnalyticsTrait
{
    /**
     * Request validation. User could be added: $validator->setValue('user_id', $user?->id)
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
     * @param array $data
     * @return string|null
     */
    public function cacheTag(array $data): ?string; // 'user:' . $data['user_id']

    /**
     * Analytics data
     *
     * @param array $data
     * @return iterable
     */
    public function getData(array $data): iterable;
}
