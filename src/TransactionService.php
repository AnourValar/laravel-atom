<?php

namespace AnourValar\LaravelAtom;

use \Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class TransactionService
{
    /**
     * Action after transaction commit
     *
     * @param callable $closure
     * @param string $connection
     * @return void
     */
    public function onCommit(callable $closure, string $connection = null) : void
    {
        if (\DB::connection($connection)->transactionLevel()) {
            \Event::listen(TransactionCommitted::class, function () use ($closure, $connection)
            {
                if (! \DB::connection($connection)->transactionLevel()) {
                    \Event::forget(TransactionCommitted::class);
                    $closure();
                }
            });

            \Event::listen(TransactionRolledBack::class, function () use ($closure, $connection)
            {
                if (! \DB::connection($connection)->transactionLevel()) {
                    \Event::forget(TransactionCommitted::class);
                    \Event::forget(TransactionRolledBack::class);
                }
            });
        } else {
            $closure();
        }
    }

    /**
     * Action after transaction rollback
     *
     * @param callable $closure
     * @param string $connection
     * @return void
     */
    public function onRollback(callable $closure, string $connection = null) : void
    {
        if (\DB::connection($connection)->transactionLevel()) {
            \Event::listen(TransactionRolledBack::class, function () use ($closure, $connection)
            {
                if (! \DB::connection($connection)->transactionLevel()) {
                    \Event::forget(TransactionRolledBack::class);
                    $closure();
                }
            });
        }
    }
}
