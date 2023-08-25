<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Exclude;

class ExcludeMapper extends Mapper
{
     public function __construct(
        #[Exclude]
        public string $a,
        public string $b,
    ) {

    }
}
