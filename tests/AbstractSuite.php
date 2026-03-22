<?php

namespace AnourValar\LaravelAtom\Tests;

abstract class AbstractSuite extends \Orchestra\Testbench\TestCase
{
    /**
     * Init
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            \AnourValar\LaravelAtom\Providers\LaravelAtomServiceProvider::class,
        ];
    }
}
