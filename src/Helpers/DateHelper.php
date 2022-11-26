<?php

namespace AnourValar\LaravelAtom\Helpers;

class DateHelper
{
    /**
     * Formatting (for display) in user's timezone: date
     *
     * @param mixed $date
     * @param mixed $time
     * @param string $timezoneClient
     * @param string|null $default
     * @return string|null
     */
    function formatDate($date, mixed $time = true, string $timezoneClient = 'UTC', ?string $default = null): ?string
    {
        if (! $date) {
            return $default;
        }

        if ($time === true && is_string($date) && ! strpos($date, ':')) {
            $time = false;
        }

        if (! $date instanceof \Carbon\CarbonInterface) {
            //try {
                $date = \Date::parse($date);
            //} catch (\Carbon\Exceptions\InvalidFormatException $e) {
            //    return $default;
            //}
        }

        if ($time) {
            if (is_string($time)) {
                $date = $date->setTimeFrom($time);
            }

            $date = $date->setTimezone($timezoneClient);
        }

        return $date->format(trans('laravel-atom::formats.date_format'));
    }

    /**
     * Formatting (for display) in user's timezone: date_time
     *
     * @param mixed $date
     * @param string $timezoneClient
     * @param string|null $default
     * @return string|null
     */
    function formatDateTime($date, string $timezoneClient = 'UTC', ?string $default = null): ?string
    {
        if (! $date) {
            return $default;
        }

        if (! $date instanceof \Carbon\CarbonInterface) {
            //try {
                $date = \Date::parse($date);
            //} catch (\Carbon\Exceptions\InvalidFormatException $e) {
            //    return $default;
            //}
        }

        return $date->setTimezone($timezoneClient)->format(trans('laravel-atom::formats.datetime_format'));
    }

    /**
     * Formatting (for display) in user's timezone: time
     *
     * @param mixed $date
     * @param string $timezoneClient
     * @param string|null $default
     * @return string|null
     */
    function formatTime($date, string $timezoneClient = 'UTC', ?string $default = null): ?string
    {
        if (! $date) {
            return $default;
        }

        if (! $date instanceof \Carbon\CarbonInterface) {
            //try {
                $date = \Date::parse($date);
            //} catch (\Carbon\Exceptions\InvalidFormatException $e) {
            //    return $default;
            //}
        }

        return $date->setTimezone($timezoneClient)->format('H:i');
    }

    /**
     * Formatting (for display) in user's timezone: relative date
     *
     * @param \Carbon\CarbonInterface $date
     * @param string $timezoneClient
     * @param string|null $default
     * @return string|null
     */
    public function formatDateRelative(
        ?\Carbon\CarbonInterface $date,
        string $timezoneClient = 'UTC',
        ?string $default = null
    ): ?string {
        if (! $date) {
            return $default;
        }

        $sourceDate = $date->setTimezone($timezoneClient)->format('Y-m-d');
        $now = now($timezoneClient);

        if ($sourceDate == $now->addDays(2)->format('Y-m-d')) {
            return trans('laravel-atom::formats.human_date.after_tomorrow');
        }

        if ($sourceDate == $now->addDays(1)->format('Y-m-d')) {
            return trans('laravel-atom::formats.human_date.tomorrow');
        }

        if ($sourceDate == $now->format('Y-m-d')) {
            return trans('laravel-atom::formats.human_date.today');
        }

        if ($sourceDate == $now->addDays(-1)->format('Y-m-d')) {
            return trans('laravel-atom::formats.human_date.yesterday');
        }

        if ($sourceDate == $now->addDays(-2)->format('Y-m-d')) {
            return trans('laravel-atom::formats.human_date.before_yesterday');
        }

        if ($date->format('Y') == $now->format('Y')) {
            return trans('laravel-atom::formats.human_date.current_year.'.$date->format('m'), ['day' => $date->format('d')]);
        }

        return $this->formatDate($date, true, $timezoneClient, $default);
    }
}
