<?php

namespace AnourValar\LaravelAtom\Helpers;

class NumberHelper
{
    /**
     * Canonize
     *
     * @param int|float|null $amount
     * @return int|null
     */
    public function encodeMultiple(int|float|null $amount): ?int
    {
        if (is_null($amount)) {
            return null;
        }

        return round($amount * config('atom.number.multiple'));
    }

    /**
     * Formatting (for display)
     *
     * @param int|null $amount
     * @return float|null
     */
    public function decodeMultiple(?int $amount): float|null
    {
        if (! isset($amount)) {
            return null;
        }

        $multiple = config('atom.number.multiple');
        return round($amount / $multiple, (mb_strlen($multiple) - 1));
    }

    /**
     * Formatting (for display)
     *
     * @param float|null $number
     * @param int $decimals
     * @param string|null $default
     * @return string|null
     */
    public function formatNumber(?float $number, int $decimals = 0, ?string $default = null): ?string
    {
        if (is_null($number)) {
            return $default;
        }

        return number_format(
            $number,
            $decimals,
            trans('laravel-atom::formats.number_format.dec_point'),
            trans('laravel-atom::formats.number_format.thousands_sep')
        );
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
