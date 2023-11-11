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
     * @param array|string $attributes
     * @return string
     */
    protected function getRawValues(array|string $attributes): string
    {
        $result = '';

        foreach ((array) $attributes as $item) {
            if ($result) {
                $result .= ', ';
            }

            $result .= \DB::escape($item);
        }

        return $result;
    }

    /**
     * Merge data from differenct sources (queries)
     *
     * @param array $sources
     * @param string $groupBy
     * @param array $structure
     * @return array
     */
    protected function mergeSources(array $sources, string $groupBy, array $structure = []): array
    {
        $result = [];

        foreach ($sources as $source) {
            foreach ($source as $item) {
                foreach ($item as $itemKey => $itemValue) {
                    if (! isset($result[$item->$groupBy])) {
                        $result[$item->$groupBy] = new \stdClass();
                    }

                    $structure[$itemKey] = (is_integer($itemValue) || is_float($itemValue)) ? 0 : null;
                    $result[$item->$groupBy]->$itemKey = $itemValue;
                }
            }
        }

        foreach ($result as &$item) {
            foreach ($structure as $structureKey => $structureValue) {
                if (! isset($item->$structureKey)) {
                    $item->$structureKey = $structureValue;
                }
            }
        }
        unset($item);

        return $result;
    }
}
