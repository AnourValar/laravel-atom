<?php

namespace AnourValar\LaravelAtom\Facades;

use Illuminate\Support\Facades\Facade;

class AtomFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \AnourValar\LaravelAtom\Service::class;
    }
}
