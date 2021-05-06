<?php

namespace AnourValar\LaravelAtom\Strategies;

use Illuminate\Database\Connection;

interface StrategyInterface
{
    /**
     * Apply strategy
     *
     * @param string $sha1
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @throws \Exception
     * @return void
     */
    public function lock(string $sha1, Connection $connection, string $table): void;
}
