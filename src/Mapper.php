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
        $names = self::getMapping($class);

        $args = [];
        foreach ($class->getConstructor()->getParameters() as $param) {
            $name = $param->getName();
            if (! property_exists(static::class, $param->getName())) {
                throw new \RuntimeException('Constructor Property Promotion must be declared for all attributes.');
            }

            $name = $names[$name];

            if ($param->isDefaultValueAvailable()) {
                $value = $data[$name] ?? $param->getDefaultValue();
            } elseif (array_key_exists($name, $data)) {
                $value = $data[$name];
            } else {
                if (stripos((string) $param->getType(), \AnourValar\LaravelAtom\Mapper\Optional::class) !== false) {
                    $value = new \AnourValar\LaravelAtom\Mapper\Optional();
                } else {
                    throw new \RuntimeException('Required parameter is missing: ' . $name);
                }
            }

            if (is_array($value) && is_subclass_of($param->getType()->getName(), self::class)) {
                $class = $param->getType()->getName();
                $value = new $class(...$value);
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
        return $this->resolveToArray((array) $this,  static::getMapping(new \ReflectionClass(static::class)));
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
     * @param array $mapping
     * @return array
     */
    protected function resolveToArray(array $data, array $mapping = []): array
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

            if ($mapping) {
                $result[$mapping[$key]] = $value;
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
    protected static function getMapping(\ReflectionClass $class): array
    {
        $result = [];

        $attributes = $class->getAttributes();
        foreach ($class->getConstructor()->getParameters() as $param) {
            $originalName = $param->getName();

            $changedName = $originalName;
            foreach (array_merge($attributes, $param->getAttributes()) as $attribute) {
                $changedName = static::handleAttribute($changedName, $attribute);
            }

            $result[$originalName] = $changedName;
        }

        return $result;
    }

    /**
     * @param string $name
     * @param \ReflectionAttribute $attribute
     * @throws \RuntimeException
     * @return string
     */
    protected static function handleAttribute(string $name, \ReflectionAttribute $attribute): string
    {
        // Mapping
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\Mapping::class) {
            $args = $attribute->getArguments();
            if (! $args) {
                throw new \RuntimeException('Mapping attribute requires a name.');
            }

            $name = array_shift($args);
        }

        // MappingSnakeCase
        if ($attribute->getName() == \AnourValar\LaravelAtom\Mapper\MappingSnakeCase::class) {
            $name = str()->snake($name);
        }


        return $name;
    }
}
