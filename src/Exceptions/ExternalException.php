<?php

namespace AnourValar\LaravelAtom\Exceptions;

use Exception;

class ExternalException extends Exception
{
    /**
     * @var array|null
     */
    protected ?array $dump;

    /**
     * @param string $action
     * @param array|null $dump
     * @return void
     */
    public function __construct(string $action, array $dump = null)
    {
        parent::__construct("Unexpected behaviour for action {$action}.");

        $this->dump = $dump;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        \Log::info($this->getMessage(), $this->dump ?? []);
    }

    /**
     * Horizon
     *
     * @return array
     */
    public function context()
    {
        return $this->dump ?? [];
    }
}
