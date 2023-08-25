<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\ArrayOf;

class ArrayOfMapper extends Mapper
{
     public function __construct(
        #[ArrayOf(ExcludeMapper::class)]
        public array $excludes,
    ) {

    }
}
