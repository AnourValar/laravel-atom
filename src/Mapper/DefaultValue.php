<?php

namespace AnourValar\LaravelAtom\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValue
{
    public function __construct(public $value)
    {

    }
}
