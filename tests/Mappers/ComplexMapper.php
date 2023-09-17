<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;

class ComplexMapper extends Mapper
{
    public function __construct(
        public int $userId,
        #[Mapping('boss_id')]
        public int $managerId,
        public NestedMapper $mapper1,
        public ?NestedMapper $mapper2 = null,
        public ?NestedMapper $mapper3 = null,
        public array $mappers4 = [],
    ) {

    }
}
