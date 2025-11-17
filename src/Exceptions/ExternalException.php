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
     * @param array|null|\AnourValar\HttpClient\Response $dump
     * @return void
     * @psalm-suppress UndefinedClass
     */
    public function __construct(string $action, array|null|\AnourValar\HttpClient\Response $dump = null)
    {
        parent::__construct("Unexpected behaviour for action {$action}.");

        if ($dump instanceof \AnourValar\HttpClient\Response) {
            $dump = $dump->dump(true);
        }
        $this->dump = $dump;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        if (mb_substr_count($this->getMessage(), 'Unexpected behaviour for action ') < 2) {
            \Log::info($this->getMessage(), $this->dump ?? []);
        }
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
