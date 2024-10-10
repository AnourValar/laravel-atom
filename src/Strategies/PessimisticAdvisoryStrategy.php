<?php

namespace AnourValar\LaravelAtom\Strategies;

use Illuminate\Database\Connection;

class PessimisticAdvisoryStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\Strategies\StrategyInterface::lock()
     */
    public function lock(string $sha1, Connection $connection): void
    {
        if (! $connection->transactionLevel()) {
            throw new \LogicException('Lock can be applied only inside transaction');
        }

        $id1 = crc32($sha1) - 2147483648;
        $id2 = crc32(strrev($sha1)) - 2147483648;

        $connection->select('SELECT pg_advisory_xact_lock(:id1, :id2)', ['id1' => $id1, 'id2' => $id2]);
    }
}
