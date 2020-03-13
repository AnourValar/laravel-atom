<?php

namespace AnourValar\LaravelAtom\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelAtomFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \AnourValar\LaravelAtom\TransactionService::class;
    }
}
