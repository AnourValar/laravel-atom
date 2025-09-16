<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;

class DateMapper extends Mapper
{
    public function __construct(
        public \Carbon\CarbonInterface $a,
        public ?\Carbon\CarbonInterface $b = null,
    ) {

    }
}
