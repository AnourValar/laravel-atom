<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\ArrayOf;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Mapper\Optional;

class OptionalMapper extends Mapper
{
    public function __construct(
        public array|Optional $a,
        #[ArrayOf(SimpleMapper::class)]
        public array|Optional $b,
    ) {

    }
}
