<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;

class DatesMapper extends Mapper
{
    public function __construct(
        public DateMapper $dates,
    ) {

    }
}
