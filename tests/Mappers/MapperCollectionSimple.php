<?php

namespace AnourValar\LaravelAtom\Tests\Mappers;

use AnourValar\LaravelAtom\MapperCollection;

class MapperCollectionSimple extends MapperCollection
{
    /**
     * {@inheritDoc}
     * @see \AnourValar\LaravelAtom\MapperCollection::mapper()
     */
    protected function mapper(): string
    {
        return SimpleMapper::class;
    }
}
