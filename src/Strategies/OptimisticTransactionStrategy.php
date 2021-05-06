<?php

namespace AnourValar\LaravelAtom\Strategies;

use Illuminate\Database\Connection;

class OptimisticTransactionStrategy extends PessimisticTransactionStrategy
{
    /**
     * @throws \AnourValar\LaravelAtom\Exceptions\OptimisticTransactionException
     *
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\Strategies\PessimisticTransactionStrategy::lock()
     */
    public function lock(string $sha1, Connection $connection, string $table): void
    {
        try {
            parent::lock($sha1, $connection, $table);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($this->isLockException($e->getMessage())) {

                throw new \AnourValar\LaravelAtom\Exceptions\OptimisticTransactionException(
                    $e->getSql(),
                    $e->getBindings(),
                    $e->getPrevious()
                );

            }

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\Strategies\TransactionStrategy::getLock()
     */
    protected function getLock()
    {
        /**
         * It's only available for:
         *     MySQL >= 8.0.1
         *     PostgreSQL => 9.5
         */

        return 'FOR UPDATE NOWAIT';
    }

    /**
     * @param string $message
     * @return boolean
     */
    private function isLockException(string $message): bool
    {
        if (strpos($message, 'NOWAIT is set')) {
            return true;
        }

        if (strpos($message, 'could not obtain lock on row in relation')) {
            return true;
        }

        return false;
    }
}
