<?php

namespace AnourValar\LaravelAtom;

class ArrayAccessMapper implements \ArrayAccess
{
    /**
     * @var array
     */
    private $data;

    /**
     * Setter
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @see magic
     *
     * @return string
     */
    public function __toString()
    {
        return '';
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
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
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
        unset($this->data[$offset]);
    }

    /**
     * @see \ArrayAccess
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return ( $this->data[$offset] ?? (new static([])) );
    }
}
