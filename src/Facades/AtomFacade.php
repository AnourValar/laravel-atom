<?php

namespace AnourValar\LaravelAtom\Facades;

use Illuminate\Support\Facades\Facade;

class AtomFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \AnourValar\LaravelAtom\Service::class;
    }
}
