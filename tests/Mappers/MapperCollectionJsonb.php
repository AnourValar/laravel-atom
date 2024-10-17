<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\MapperCollection;
use AnourValar\LaravelAtom\Mapper\Jsonb;

#[Jsonb]
class MapperCollectionJsonb extends MapperCollection
{
    /**
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\MapperCollection::mapper()
     */
    protected function mapper(): string
    {
        return JsonMapper::class;
    }
}
