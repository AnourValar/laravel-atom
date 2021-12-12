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
     * @return void
     */
    public function push(string $event, string $connectionName, callable $closure): void
    {
        $this->tasks[$event][$connectionName][] = $closure;
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
