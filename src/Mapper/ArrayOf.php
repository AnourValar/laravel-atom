<?php

namespace AnourValar\LaravelAtom\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayOf
{
    public function __construct(public $mapper)
    {

    }
}
