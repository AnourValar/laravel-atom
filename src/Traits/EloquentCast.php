<?php

namespace AnourValar\LaravelAtom\Traits;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

trait EloquentCast
{
    /**
     * Eloquent Casts support
     *
     * @param array $arguments
     * @return CastsAttributes
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        $class = static::class;
        return new class ($class) implements CastsAttributes {
            public function __construct(private string $class)
            {
                //
            }

            public function get(Model $model, string $key, mixed $value, array $attributes)
            {
                if (! isset($value)) {
                    return null;
                }

                return ($this->class)::from(json_decode($value, true));
            }

            public function set(Model $model, string $key, mixed $value, array $attributes)
            {
                if (! isset($value)) {
                    return null;
                }

                return json_encode(($this->class)::from($value)->toArray(), JSON_UNESCAPED_UNICODE);
            }

            public function serialize(Model $model, string $key, mixed $value, array $attributes): array
            {
                return $value->toArray();
            }
        };
    }

    /**
     * JSONB sort
     *
     * @param mixed $value
     * @return mixed
     */
    protected function sort(mixed $value): mixed
    {
        if (is_array($value) && ! array_is_list($value)) {
            uksort($value, function ($a, $b) {
                $strlenA = mb_strlen($a);
                $strlenB = mb_strlen($b);

                if ($strlenA == $strlenB) {
                    return $a <=> $b;
                }

                return $strlenA <=> $strlenB;
            });
        }

        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = $this->sort($item);
            }
            unset($item);
        }

        return $value;
    }
}
