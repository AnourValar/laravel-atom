<?php

namespace AnourValar\LaravelAtom\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mapping
{
    public function __construct(public string $name)
    {

    }
}
