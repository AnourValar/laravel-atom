<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Mapper\Optional;

class NestedMapper extends Mapper
{
    public function __construct(
        public string|Optional $a,
    ) {

    }
}
