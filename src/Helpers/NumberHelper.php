<?php

namespace AnourValar\LaravelAtom\Helpers;

class NumberHelper
{
    /**
     * Normalize
     *
     * @param int|float|null $amount
     * @return int|null
     */
    public function encodeMultiple(int|float|null $amount): ?int
    {
        if (is_null($amount)) {
            return null;
        }

        return round(round($amount * config('atom.number.multiple'), 1));
    }

    /**
     * Format (for display)
     *
     * @param null|int|float $amount
     * @return float|null
     */
    public function decodeMultiple(null|int|float $amount): float|null
    {
        if (! isset($amount)) {
            return null;
        }

        $multiple = config('atom.number.multiple');
        return round($amount / $multiple, (mb_strlen($multiple) - 1));
    }

    /**
     * Amount as text
     *
     * @param int|float $amount
     * @param bool $ucfirst
     * @return string
     */
    public function spellout(int|float $amount, bool $ucfirst = true): string
    {
        $int = (int) $amount;
        $dec = (int) round(($amount - floor($amount)) * 100);

        $spelloutInt = \Lang::choice(
            'laravel-atom::formats.spellout_int',
            $int,
            ['int' => (new \MessageFormatter(\App::getLocale(), '{n, spellout}'))->format(['n' => $int])]
        );
        $spelloutDec = \Lang::choice(
            'laravel-atom::formats.spellout_dec',
            $dec,
            ['dec' => str_pad((string) $dec, 2, '0', STR_PAD_LEFT)]
        );

        $result = trans('laravel-atom::formats.spellout', ['spellout_int' => $spelloutInt, 'spellout_dec' => $spelloutDec]);
        if ($ucfirst) {
            $result = \Str::ucfirst($result);
        }

        return $result;
    }
}
