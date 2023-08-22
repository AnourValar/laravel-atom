<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;

#[Mapping(MappingSnakeCase::class)]
class ModeMapper extends Mapper
{
     public function __construct(
        public int $userId,
        public int $managerId,
    ) {

    }
}
