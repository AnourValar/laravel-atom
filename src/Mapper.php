<?php

namespace AnourValar\LaravelAtom;

abstract class Mapper implements \JsonSerializable, \ArrayAccess
{
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

            if (is_array($value) && is_subclass_of($param->getType()->getName(), self::class)) {
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
            }

            $args[] = $value;
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
        return $this->resolveToArray((array) $this,  static::getRules(new \ReflectionClass(static::class)));
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
            $args = $attribute->getArguments();
            if (! $args) {
                throw new \RuntimeException('Mapping attribute requires a name.');
            }

            return ['name' => array_shift($args)];
        }

        // MappingSnakeCase
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\MappingSnakeCase::class) {
            return ['name' => str()->snake($rule['name'])];
        }

        // DefaultValue
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\DefaultValue::class) {
            $args = $attribute->getArguments();
            if (! $args) {
                throw new \RuntimeException('DefaultValue attribute requires a value.');
            }

            return ['default_value' => array_shift($args)];
        }

        // Cast
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Cast::class) {
            $args = $attribute->getArguments();
            if (! $args) {
                throw new \RuntimeException('Cast attribute requires a value.');
            }

            return ['cast' => array_shift($args)];
        }

        // Mutate
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Mutate::class) {
            $args = $attribute->getArguments();
            if (! $args) {
                throw new \RuntimeException('Mutate attribute requires a value.');
            }

            return ['mutate' => array_shift($args)];
        }


        return [];
    }
}
