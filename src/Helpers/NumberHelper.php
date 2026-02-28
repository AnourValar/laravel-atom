<?php

namespace AnourValar\LaravelAtom\Helpers;

class NumberHelper
{
    /**
     * Encode to multiple
     *
     * @param string|int|float|null $amount
     * @return string|null
     */
    public function encodeMultiple(string|int|float|null $amount): ?string
    {
        if (is_null($amount)) {
            return null;
        }

        return bcmul($amount, config('atom.number.multiple'));
    }

    /**
     * Decode from multiple
     *
     * @param string|int|null $amount
     * @return string|null
     */
    public function decodeMultiple(string|int|null $amount): ?string
    {
        if (! isset($amount)) {
            return null;
        }

        $multiple = config('atom.number.multiple');
        return rtrim(rtrim(bcdiv($amount, $multiple, (mb_strlen($multiple) - 1)), '0'), '.');
    }

    /**
     * Decode multiple & format
     *
     * @param string|int|float|null $amount
     * @param string|null $locale
     * @param bool $trim
     * @return string|null
     */
    public function formatMultiple(string|int|float|null $amount, ?string $locale = null, bool $trim = true): ?string
    {
        if (! isset($amount)) {
            return null;
        }

        $multiple = config('atom.number.multiple');
        $amount = bcdiv($amount, $multiple, (mb_strlen($multiple) - 1));
        [$decPoint, $thousandsSep] = $this->numberDelimiters($locale);

        $amount = $this->numberFormat($amount, (mb_strlen($multiple) - 1), $decPoint, $thousandsSep);
        if ($trim) {
            $amount = rtrim(rtrim($amount, '0'), $decPoint);
        }

        return $amount;
    }

    /**
     * Format number
     *
     * @param string|int|float|null $number
     * @param int $precision
     * @param string|null $locale
     * @param bool $trim
     * @return string|null
     */
    public function formatNumber(string|int|float|null $number, int $precision = 2, ?string $locale = null, bool $trim = true): ?string
    {
        if (! isset($number)) {
            return null;
        }

        [$decPoint, $thousandsSep] = $this->numberDelimiters($locale);

        $amount = $this->numberFormat($number, $precision, $decPoint, $thousandsSep);
        if ($trim) {
            $amount = rtrim(rtrim($amount, '0'), $decPoint);
        }

        if ($amount === '') {
            return '0';
        }

        return $amount;
    }

    /**
     * @param string|null $locale
     * @return array
     */
    protected function numberDelimiters(?string $locale): array
    {
        $formatter = new \NumberFormatter($locale ?? config('app.locale'), \NumberFormatter::DECIMAL);

        $decPoint = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $thousandsSep = $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

        return [$decPoint, $thousandsSep];
    }

    /**
     * BC Math "number_format"
     *
     * @param string $number
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    protected function numberFormat(string $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
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
        $intFormatted = implode('@', str_split($intPartReversed, 3));
        $intFormatted = strrev($intFormatted);
        $intFormatted = str_replace('@', $thousandsSep, $intFormatted); // utf friendly

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
