<?php

namespace AnourValar\LaravelAtom\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public function __construct(public $castType)
    {

    }
}
