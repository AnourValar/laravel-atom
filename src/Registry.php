<?php

namespace AnourValar\LaravelAtom;

class Registry
{
    /**
     * @var array
     */
    private $tasks;

    /**
     * @param string $event
     * @param string $connectionName
     * @param callable $closure
     * @param string|null $key
     * @return int|string
     */
    public function push(string $event, string $connectionName, callable $closure, ?string $key = null): int|string
    {
        if (isset($key)) {
            unset($this->tasks[$event][$connectionName][$key]);
            $this->tasks[$event][$connectionName][$key] = $closure;
            return $key;
        }

        $this->tasks[$event][$connectionName][] = $closure;
        return array_key_last($this->tasks[$event][$connectionName]);
    }

    /**
     * @param string $event
     * @param string $connectionName
     * @param int|string $key
     * @return void
     */
    public function remove(string $event, string $connectionName, int|string $key): void
    {
        unset($this->tasks[$event][$connectionName][$key]);
    }

    /**
     * @param string $event
     * @param string $connectionName
     * @return array
     */
    public function pull(string $event, string $connectionName): array
    {
        if (isset($this->tasks[$event][$connectionName])) {
            $tasks = $this->tasks[$event][$connectionName];
            unset($this->tasks[$event][$connectionName]);

            return $tasks;
        }

        return [];
    }
}
