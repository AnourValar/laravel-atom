<?php

namespace AnourValar\LaravelAtom\Mapper;

class Optional
{
    /**
     * Placeholder
     *
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * Condition helper
     *
     * @return bool
     */
    public function exists(): bool
    {
        return false;
    }
}
