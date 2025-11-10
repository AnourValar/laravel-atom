<?php

namespace AnourValar\LaravelAtom\Helpers;

class NumberHelper
{
    /**
     * Encode to integer
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
     * Decode to float
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
     * Decode to string
     *
     * @param null|int|float|string $amount
     * @return string|null
     */
    public function formatMultiple(null|int|float|string $amount): string|null
    {
        if (! isset($amount)) {
            return null;
        }

        $multiple = config('atom.number.multiple');
        $amount = bcdiv($amount, $multiple, (mb_strlen($multiple) - 1));

        return rtrim(rtrim($this->numberFormat($amount, (mb_strlen($multiple) - 1), '.', ''), '0'), '.');
    }

    /**
     * BC Math fiendly "number_format"
     *
     * @param string $number
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function numberFormat(string $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
    {
        if ($number === '') {
            return '';
        }

        if (stripos($number, 'e') !== false) {
            return number_format($number, $decimals, $decPoint, $thousandsSep);
        }

        $sign = '';
        if ($number[0] === '-') {
            $sign = '-';
            $number = substr($number, 1);
        }

        [$intPart, $fracPart] = array_pad(explode('.', $number, 2), 2, '');

        if ($decimals > 0) {
            $fracPart = str_pad(substr($fracPart, 0, $decimals), $decimals, '0');
        } else {
            $fracPart = '';
        }

        $intPartReversed = strrev($intPart);
        $intFormatted = implode($thousandsSep, str_split($intPartReversed, 3));
        $intFormatted = strrev($intFormatted);

        return $sign . $intFormatted . ($decimals > 0 ? $decPoint . $fracPart : '');
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
