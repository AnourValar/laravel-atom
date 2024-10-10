<?php

namespace AnourValar\LaravelAtom\Strategies;

use Illuminate\Database\Connection;

class OptimisticAdvisoryStrategy implements StrategyInterface
{
    /**
     * @throws \AnourValar\LaravelAtom\Exceptions\OptimisticException
     *
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

        if (! $connection->select('SELECT pg_try_advisory_xact_lock(:id1, :id2)', ['id1' => $id1, 'id2' => $id2])[0]->pg_try_advisory_xact_lock) {
            throw new \AnourValar\LaravelAtom\Exceptions\OptimisticException();
        }
    }
}
