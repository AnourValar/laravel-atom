<?php

namespace AnourValar\LaravelAtom;

abstract class Repository
{
    /**
     * Get placeholders: ?, ?, ?
     *
     * @param array $attributes
     * @return string
     */
    protected function getPlaceholders(array $attributes): string
    {
        return implode(', ', array_fill(0, count($attributes), '?'));
    }

    /**
     * Get bindings: ['foo', 'bar', 'baz']
     *
     * @param array $attributes
     * @return array
     */
    protected function getBindings(array $attributes): array
    {
        return array_values($attributes);
    }

    /**
     * Get SQL values: 'foo', 'bar', 'baz'
     *
     * @param array $attributes
     * @return string
     */
    protected function getRawValues(array $attributes): string
    {
        $result = '';

        foreach ($attributes as $item) {
            if ($result) {
                $result .= ', ';
            }

            $result .= \DB::escape($item);
        }

        return $result;
    }
}
