<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\Mapper;

class JsonMapper extends Mapper
{
    public function __construct(
        public string $aa,
        public string $a,
    ) {

    }
}
