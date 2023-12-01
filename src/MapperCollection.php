<?php

namespace AnourValar\LaravelAtom;

abstract class MapperCollection implements \Iterator, \JsonSerializable, \ArrayAccess
{
    /**
     * Mapper class
     *
     * @return string
     */
    abstract protected function mapper(): string;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @param array $data
     * @return void
     * @throws \RuntimeException
     */
    public function __construct(array $data)
    {
        $mapper = $this->mapper();
        if (! is_subclass_of($mapper, \AnourValar\LaravelAtom\Mapper::class)) {
            throw new \RuntimeException('mapper() method must return \AnourValar\LaravelAtom\Mapper compatibility class.');
        }

        foreach ($data as &$item) {
            if (! is_object($item) || get_class($item) != $mapper) {
                $item = $mapper::from($item);
            }
        }
        unset($item);

        $this->data = $data;
    }

    /**
     * Create an object from the input
     *
     * @param array $data
     * @return static
     */
    public static function from(array $data): static
    {
        return new static($data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->data as $key => $item) {
            $result[$key] = $item->toArray();
        }

        return $result;
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
     * @see \Traversable
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * @see @see \Traversable
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * @see \Traversable
     *
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @see \Traversable
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @see \Traversable
     *
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->data) !== null;
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
        return isset($this->data[$offset]);
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
        return $this->data[$offset];
    }
}
