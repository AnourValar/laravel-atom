<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Jsonb;

#[Jsonb]
class JsonbMapper extends Mapper
{
    public function __construct(
        public string $aa,
        public string $a,
    ) {

    }
}
