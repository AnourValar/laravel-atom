<?php

namespace AnourValar\LaravelAtom;

class MapperLazy implements \Iterator, \Countable
{
    /**
     * @var array|null
     */
    private $data;

    /**
     * @var callable|null
     */
    private $handler;

    /**
     * Setter
     *
     * @param callable $handler
     * @return void
     */
    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @see \Countable
     *
     * @return int
     */
    public function count(): int
    {
        $this->lazy();

        return count($this->data);
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
        $this->lazy();

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
     * @return void
     */
    private function lazy()
    {
        if (! isset($this->data)) {
            $handler = $this->handler;
            $this->data = $handler();
            $this->handler = null;
        }
    }
}
