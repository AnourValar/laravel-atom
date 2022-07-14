<?php

namespace AnourValar\LaravelAtom\Helpers;

class ArrayHelper
{
    /**
     * json_encode pretty mode
     *
     * @param array $value
     * @param bool $multiline
     * @return string
     */
    function jsonPretty(array $value, bool $multiline = true): string
    {
        $result = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! $multiline) {
            $result = preg_replace('#\s+#', ' ', $result);
        }

        return $result;
    }

    /**
     * Export array (PSR style)
     *
     * @param array $array
     * @param bool $inlineAvailable
     * @param int $indentSize
     * @return string
     */
    public function export(array $array, bool $inlineAvailable = true, int $indentSize = 4): string
    {
        $result = '';

        $isAssoc = \Arr::isAssoc($array);
        $isInline = ($inlineAvailable && $this->shouldBeInline($array));
        $isFirst = true;

        foreach ($array as $key => $value) {
            if ($result && !$isInline) {
                $result .= "\n";
            }

            $key = "'".addcslashes($key, "'")."'";

            if (is_array($value)) {

                $result .= str_pad('', $indentSize, ' ', STR_PAD_LEFT) . "$key => [";

                $sub = $this->export($value, $inlineAvailable, $indentSize + 4);

                if ($sub) {
                    if (stripos($sub, "\n")) {
                        $result .= "\n" . $sub . "\n" . str_pad('', $indentSize, ' ', STR_PAD_LEFT) . "],";
                    } else {
                        $result .= trim(mb_substr($sub, 0, -1))."],";
                    }
                } else {
                    $result .= "],";
                }

            } else {

                if (is_null($value)) {
                    $value = 'null';
                } elseif (is_string($value)) {
                    $value = "'".addcslashes($value, "'")."'";
                }

                if (!$isInline || $isFirst) {
                    $result .= str_pad('', $indentSize, ' ', STR_PAD_LEFT);
                    $isFirst = false;
                } else {
                    $result .= ' ';
                }

                if ($isAssoc) {
                    $result .= "$key => $value,";
                } else {
                    $result .= "$value,";
                }

            }
        }

        return $result;
    }

    /**
     * Compares structure of two arrays
     *
     * @param array $value1
     * @param array $value2
     * @param array $exceptions
     * @param array $path
     * @return string|NULL
     */
    public function getStructureDiff(array $value1, array $value2, array $exceptions, array $path = []): ?string
    {
        $diff = array_keys(array_diff_key($value1, $value2));
        foreach ($diff as $key) {
            if (in_array($key, $exceptions)) {
                continue;
            }

            if (! is_integer($key)) {
                return implode('.', array_merge($path, [$key]));
            }
        }

        foreach (array_keys($value2) as $key) {
            if (in_array($key, $exceptions)) {
                continue;
            }

            if (isset($value1[$key]) && is_array($value2[$key]) && is_array($value1[$key])) {
                $diff = $this->getStructureDiff($value1[$key], $value2[$key], $exceptions, array_merge($path, [$key]));

                if ($diff) {
                    return $diff;
                }
            }
        }

        return null;
    }

    /**
     * Compares data types of two arrays
     *
     * @param array $value1
     * @param array $value2
     * @param array $path
     * @return string|NULL
     */
    public function getTypeDiff(array $value1, array $value2, array $path = []): ?string
    {
        foreach (array_keys($value2) as $key) {
            if (! array_key_exists($key, $value1)) {
                continue;
            }

            if (is_array($value2[$key]) && !is_array($value1[$key])) {
                return implode('.', array_merge($path, [$key]));
            }

            if (is_bool($value2[$key]) && !is_bool($value1[$key])) {
                return implode('.', array_merge($path, [$key]));
            }

            if (is_numeric($value2[$key]) && !is_numeric($value1[$key])) {
                return implode('.', array_merge($path, [$key]));
            }

            if (is_array($value2[$key])) {
                $diff = $this->getTypeDiff($value1[$key], $value2[$key], array_merge($path, [$key]));

                if ($diff) {
                    return $diff;
                }
            }
        }

        return null;
    }

    /**
     * Recursively merges arrays with replaces for scalar values
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public function mergeRecursive(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (! array_key_exists($key, $array1)) {
                $array1[$key] = $value;
                continue;
            }

            if (is_array($array1[$key]) && is_array($value)) {
                if (\Arr::isList($array1[$key]) && \Arr::isList($value)) {
                    $array1[$key] = array_merge($array1[$key], $value);
                    continue;
                }

                $array1[$key] = $this->mergeRecursive($array1[$key], $value);
                continue;
            }

            $array1[$key] = $value;
        }

        return $array1;
    }

    /**
     * Quick response builder
     *
     * @param object $data
     * @param array $attributes
     * @return mixed
     */
    function publishCollection(object $data, array $attributes): mixed
    {
        $appends = null;
        $relations = null;
        $apply = function ($item) use (&$attributes, &$appends, &$relations) {
            if (is_array($item)) {
                return array_filter(
                    $item,
                    function ($item) use ($attributes) {
                        return in_array($item, $attributes, true);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }

            if (is_null($appends)) {
                $appends = [];

                foreach ($attributes as $attribute) {
                    if (! array_key_exists($attribute, $item->getAttributes()) && stripos($attribute, '.') === false) {
                        $appends[] = $attribute;
                    }
                }
            }

            if (is_null($relations)) {
                $relations = [];
                foreach ($attributes as $name => $attribute) {
                    $patterns = explode('.', $attribute, 2);

                    if ($patterns[0] != $attribute) {
                        $relations[$patterns[0]][] = $patterns[1];

                        if (! in_array($patterns[0], $attributes)) {
                            $attributes[] = $patterns[0];
                        }
                        unset($attributes[$name]);
                    }
                }
            }

            foreach ($relations as $key => $value) {
                if ($item->relationLoaded($key) && isset($item->$key)) {
                    $this->publishCollection($item->$key, $value);
                }
            }

            return $item->setVisible($attributes)->append($appends);
        };

        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            return $apply($data)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return $data
            ->values()
            ->transform(function ($item) use ($apply) {
                if ($item instanceof \Illuminate\Database\Eloquent\Collection) {
                    return $item->transform(function ($item) use ($apply) {
                        return $apply($item);
                    });
                } else {
                    return $apply($item);
                }
            })
            ->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $array
     * @return boolean
     */
    private function shouldBeInline(array $array): bool
    {
        $isAssoc = \Arr::isAssoc($array);
        $length = 0;

        foreach ($array as $key => $value) {
            if (is_array($value) && count($value)) {
                return false;
            }

            if (is_scalar($value)) {
                $length += mb_strlen($value);

                if ($isAssoc) {
                    $length += mb_strlen($key) + 4;
                }
            }
        }

        if ($length > 120) {
            return false;
        }

        return true;
    }
}
