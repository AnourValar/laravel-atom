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
     * @param string $sha1
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @param bool $reTry
     * @throws \Exception
     * @return void
     */
    protected function apply(string $sha1, Connection $connection, string $table, bool $reTry = true): void
    {
        $connection->beginTransaction();

        try {
            $record = $connection->table($table)->lock($this->getLock())->where('sha1', '=', $sha1)->first();
        } catch (\Illuminate\Database\QueryException $e) {
            $connection->rollBack();
            throw $e;
        }

        if ($record) {
            $connection->table($table)->where('sha1', '=', $sha1)->update(['updated_at' => date('Y-m-d H:i:s')]);

            $connection->commit();
            return;
        }

        if (! $reTry) {
            $connection->rollBack();
            throw new \Exception('Something went wrong.');
        }

        try {
            $connection->table($table)->insert(['sha1' => $sha1, 'updated_at' => date('Y-m-d H:i:s')]);
            $connection->commit();
        } catch (\Illuminate\Database\QueryException $e) {
            $connection->rollBack();
        } catch (\Throwable $e) {
            $connection->rollBack();

            throw $e;
        }

        $this->apply($sha1, $connection, $table, false);
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
     * @return bool
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
