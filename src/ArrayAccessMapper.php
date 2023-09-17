<?php

namespace AnourValar\LaravelAtom;

class ArrayAccessMapper implements \ArrayAccess
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $instanceInsteadOfNull;

    /**
     * Setter
     *
     * @param array $data
     * @param bool $instanceInsteadOfNull
     * @return void
     */
    public function __construct(array $data, bool $instanceInsteadOfNull = false)
    {
        $this->data = $data;
        $this->instanceInsteadOfNull = $instanceInsteadOfNull;
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
     * Check if at least one key exists (not null)
     *
     * @return bool
     */
    public function has()
    {
        foreach (func_get_args() as $arg) {
            if (isset($this->data[$arg])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if all keys exist (not null)
     *
     * @return bool
     */
    public function hasAll()
    {
        foreach (func_get_args() as $arg) {
            if (! isset($this->data[$arg])) {
                return false;
            }
        }

        return true;
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
        return ($this->data[$offset] ?? ($this->instanceInsteadOfNull ? (new static([], true)) : null));
    }
}
