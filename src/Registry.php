<?php

namespace AnourValar\LaravelAtom;

class Registry
{
    /**
     * @var array
     */
    private static $tasks;

    /**
     * Singleton
     */
    private function __construct()
    {
        //
    }

    /**
     * @param string $event
     * @param string $connectionName
     * @param callable $closure
     * @return void
     */
    public static function push(string $event, string $connectionName, callable $closure) : void
    {
        self::$tasks[$event][$connectionName][] = $closure;
    }

    /**
     * @param string $event
     * @param string $connectionName
     * @return array
     */
    public static function pull(string $event, string $connectionName) : array
    {
        $list = ( self::$tasks[$event][$connectionName] ?? [] );
        unset(self::$tasks[$event][$connectionName]);

        return $list;
    }
}
