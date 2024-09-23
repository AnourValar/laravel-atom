<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Mapper\Jsonb;

#[Jsonb]
class SimpleMapper extends Mapper
{
    public function __construct(
        public string $a,
        public $b,
        public ?string $c = null,
        public int $d = 1,
    ) {

    }
}
