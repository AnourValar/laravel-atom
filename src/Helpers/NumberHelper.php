<?php

namespace AnourValar\LaravelAtom\Helpers;

class NumberHelper
{
    /**
     * Canonize
     *
     * @param int|float|string|null $amount
     * @return int|null
     */
    public function encodeMultiple(int|float|string|null $amount): ?int
    {
        if (is_null($amount)) {
            return null;
        }

        return round($amount, config('atom.number.multiple_round')) * config('atom.number.multiple');
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

        return round($amount / config('atom.number.multiple'), config('atom.number.multiple_round'));
    }
}
