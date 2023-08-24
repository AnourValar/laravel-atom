<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Mapper\DefaultValue;
use AnourValar\LaravelAtom\Mapper\Cast;
use AnourValar\LaravelAtom\Mapper\Mutate;
use AnourValar\LaravelAtom\Mapper\Optional;

#[MappingSnakeCase]
class ModeMapper extends Mapper
{
     public function __construct(
        public int $userId,
        #[DefaultValue(3)]
        public int $managerId,
        #[Cast('array')]
        public array $ids,
        #[Mutate('trim')]
        public string|Optional $name,
    ) {

    }
}
