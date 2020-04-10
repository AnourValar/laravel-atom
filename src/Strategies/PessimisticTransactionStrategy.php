<?php

namespace AnourValar\LaravelAtom\Strategies;

use Illuminate\Database\Connection;

class PessimisticTransactionStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\Strategies\StrategyInterface::lock()
     */
    public function lock(string $sha1, Connection $connection, string $table) : void
    {
        if (! $connection->transactionLevel()) {
            throw new \Exception('Lock can be applied only inside transaction');
        }

        $this->apply($sha1, $connection, $table);
    }

    /**
     * @param string $sha1
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @param boolean $reTry
     * @throws \Exception
     */
    protected function apply(string $sha1, Connection $connection, string $table, bool $reTry = true) : void
    {
        $record = $connection->table($table)->lock($this->getLock())->where('sha1', '=', $sha1)->first();

        if ($record) {
            $connection->table($table)->where('sha1', '=', $sha1)->update(['updated_at' => date('Y-m-d H:i:s')]);

            return;
        }

        if (! $reTry) {
            throw new \Exception('Something went wrong.');
        }

        try {
            $connection->beginTransaction();
            $connection->table($table)->insert(['sha1' => $sha1, 'updated_at' => date('Y-m-d H:i:s')]);
            $connection->commit();
        } catch (\Illuminate\Database\QueryException $e) {
            $connection->rollback();
        }

        $this->apply($sha1, $connection, $table, false);
    }

    /**
     * @return mixed
     */
    protected function getLock()
    {
        return true; // FOR UPDATE
    }
}
