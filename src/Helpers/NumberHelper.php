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
}
