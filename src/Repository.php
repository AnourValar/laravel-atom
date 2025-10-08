<?php

namespace AnourValar\LaravelAtom;

use Carbon\CarbonInterface;

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

    /**
     * Object to array conversion
     *
     * @param mixed $data
     * @return mixed
     */
    protected function toArray($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (is_array($data)) {
            $data = array_map(fn ($item) => $this->toArray($item), $data);
        }

        return $data;
    }

    /**
     * Gap the missing dates with defaults
     *
     * @param array $data
     * @param \Carbon\CarbonInterface $dateFrom
     * @param \Carbon\CarbonInterface $dateTo
     * @param array|callable $defaults
     * @param string $format
     * @return array
     * @throws \RuntimeException
     */
    protected function gap(array $data, CarbonInterface $dateFrom, CarbonInterface $dateTo, array|callable $defaults, string $format = 'Y-m-d'): array
    {
        $result = [];

        if (is_callable($defaults)) {
            $defaults = $defaults();
        }

        $dateColumn = null;
        $groupBy = [];
        foreach ($defaults[0] as $key => $value) {
            if (! isset($value)) {
                $dateColumn = $key;
            }

            if (! in_array($value, [0, 0.0, '0', '0.0'], true)) {
                $groupBy[] = $key;
            }
        }
        if (! isset($dateColumn)) {
            throw new \RuntimeException('Date attribute is not present.');
        }

        while ($dateFrom <= $dateTo) {
            foreach ($defaults as $default) {
                $default[$dateColumn] = $dateFrom->format($format);
                $result[$this->getHash($default, $groupBy)] = $default;
            }

            $dateFrom = $dateFrom->addDay();
        }

        foreach ($data as $item) {
            $item = (array) $item;
            $result[$this->getHash($item, $groupBy)] = $item;
        }

        return array_values($result);
    }

    /**
     * Ungroup part of aggregations
     *
     * @param array $data
     * @param array $groupBy
     * @param array $aggregations
     * @return array
     */
    protected function ungroup(array $data, array $groupBy = [], array $aggregations = ['qty']): array
    {
        $result = [];

        foreach ($data as $item) {
            $key = '';
            foreach ($groupBy as $curr) {
                $key .= $item[$curr].'#';
            }

            foreach ($aggregations as $curr) {
                $result[$key][$curr] ??= 0;
                $result[$key][$curr] += $item[$curr];
            }

            foreach ($groupBy as $curr) {
                $result[$key][$curr] = $item[$curr];
            }
        }

        if (! $result) {
            return $result;
        }

        if (! $groupBy) {
            return $result[''];
        }

        return array_values($result);
    }

    /**
     * @param array $data
     * @param array $groupBy
     * @return string
     */
    private function getHash(array $data, array $groupBy): string
    {
        $data = array_intersect_key($data, array_fill_keys($groupBy, true));
        return sha1(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
