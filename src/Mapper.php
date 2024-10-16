<?php

namespace AnourValar\LaravelAtom;

use Illuminate\Contracts\Database\Eloquent\Castable;

abstract class Mapper implements \JsonSerializable, \ArrayAccess, Castable
{
    use \AnourValar\LaravelAtom\Traits\EloquentCast;

    /**
     * @throws \RuntimeException
     * @return void
     */
    public function __construct()
    {
        throw new \RuntimeException('Construct must be declared.');
    }

    /**
     * Create an object from the input
     *
     * @param array|object $data
     * @throws \RuntimeException
     * @return static
     */
    public static function from(array|object $data): static
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }

        $class = new \ReflectionClass(static::class);
        $rules = self::getRules($class);

        $args = [];
        foreach ($class->getConstructor()->getParameters() as $param) {
            $name = $param->getName();
            if (! property_exists(static::class, $param->getName())) {
                throw new \RuntimeException('Constructor Property Promotion must be declared for all attributes.');
            }

            $rule = $rules[$name];

            if ($param->isDefaultValueAvailable()) {
                $value = $data[$rule['name']] ?? $param->getDefaultValue();
            } elseif (array_key_exists($rule['name'], $data)) {
                $value = $data[$rule['name']];
            } elseif (array_key_exists('default_value', $rule)) {
                $value = $rule['default_value'];
            } else {
                if (stripos((string) $param->getType(), \AnourValar\LaravelAtom\Mapper\Optional::class) !== false) {
                    $value = new \AnourValar\LaravelAtom\Mapper\Optional();
                } else {
                    throw new \RuntimeException('Required parameter is missing: ' . $rule['name']);
                }
            }

            if (is_array($value) && ! $param->getType() instanceof \ReflectionUnionType && is_subclass_of($param->getType()->getName(), self::class)) {
                $class = $param->getType()->getName();
                $value = $class::from($value);
            }

            if (! $value instanceof \AnourValar\LaravelAtom\Mapper\Optional) {
                if (isset($rule['cast'])) {
                    settype($value, $rule['cast']);
                }

                if (isset($rule['mutate'])) {
                    $value = $rule['mutate']($value);
                }

                if (isset($rule['array_of']) && isset($value)) {
                    $arrayOf = $rule['array_of'];
                    foreach ($value as &$item) {
                        if ($item instanceof $arrayOf) {
                            continue;
                        }

                        $item = $arrayOf::from($item);
                    }
                    unset($item);
                }
            }

            $args[] = $value;
            unset($data[$rule['name']]);
        }

        if ($data && ! \App::isProduction()) {
            throw new \RuntimeException('Unused attributes: ' . implode(', ', array_keys($data)));
        }

        return new static(...$args);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = $this->resolveToArray((array) $this, static::getRules(new \ReflectionClass(static::class)));

        foreach ((new \ReflectionClass(static::class))->getAttributes() as $attribute) {
            if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Jsonb::class) {
                $result = $this->sort($result);
            }
        }

        return $result;
    }

    /**
     * Get data (filtered)
     *
     * @param array|string $keys
     * @return array
     */
    public function only(array|string $keys): array
    {
        $keys = (array) $keys;

        return array_filter(
            $this->toArray(),
            function ($key) use ($keys) {
                return in_array($key, $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @see \JsonSerializable
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @see \ArrayAccess
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        throw new \RuntimeException('Incorrect usage.');
    }

    /**
     * @see \ArrayAccess
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * @see \ArrayAccess
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        throw new \RuntimeException('Incorrect usage.');
    }

    /**
     * @see \ArrayAccess
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->$offset;
    }

    /**
     * @param array $data
     * @param array $rules
     * @return array
     */
    protected function resolveToArray(array $data, array $rules = []): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($rules && ! empty($rules[$key]['exclude'])) {
                continue;
            }

            if (is_object($value)) {
                if ($value instanceof \AnourValar\LaravelAtom\Mapper\Optional) {
                    unset($data[$key]);
                    continue;
                } elseif ($value instanceof \Stringable) {
                    $value = (string) $value;
                } else {
                    $value = $value->toArray();
                }
            }

            if (is_array($value)) {
                $value = $this->resolveToArray($value);
            }

            if ($rules) {
                $result[$rules[$key]['name']] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param \ReflectionClass $class
     * @return array
     */
    protected static function getRules(\ReflectionClass $class): array
    {
        $result = [];

        $attributes = $class->getAttributes();
        foreach ($class->getConstructor()->getParameters() as $param) {
            $originalName = $param->getName();

            $result[$originalName] = ['name' => $originalName];
            foreach (array_merge($attributes, $param->getAttributes()) as $attribute) {
                $result[$originalName] = array_replace(
                    $result[$originalName],
                    static::handleAttribute($result[$originalName], $attribute)
                );
            }
        }

        return $result;
    }

    /**
     * @param array $rule
     * @param \ReflectionAttribute $attribute
     * @throws \RuntimeException
     * @return array
     */
    protected static function handleAttribute(array $rule, \ReflectionAttribute $attribute): array
    {
        // Mapping
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Mapping::class) {
            return ['name' => self::getArg($attribute)];
        }

        // MappingSnakeCase
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\MappingSnakeCase::class) {
            return ['name' => str()->snake($rule['name'])];
        }

        // DefaultValue
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\DefaultValue::class) {
            return ['default_value' => self::getArg($attribute)];
        }

        // Cast
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Cast::class) {
            return ['cast' => self::getArg($attribute)];
        }

        // Mutate
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Mutate::class) {
            return ['mutate' => self::getArg($attribute)];
        }

        // Exclude
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Exclude::class) {
            return ['exclude' => true];
        }

        // ArrayOf
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\ArrayOf::class) {
            $arg = self::getArg($attribute);
            if (! is_subclass_of($arg, self::class)) {
                throw new \RuntimeException('ArrayOf unsupported type.');
            }

            return ['array_of' => $arg];
        }


        return [];
    }

    /**
     * @param \ReflectionAttribute $attribute
     * @throws \RuntimeException
     * @return mixed
     */
    protected static function getArg(\ReflectionAttribute $attribute)
    {
        $args = $attribute->getArguments();
        if (! $args) {
            throw new \RuntimeException($attribute->getName() . ' attribute requires an argument.');
        }

        return array_shift($args);
    }
}
