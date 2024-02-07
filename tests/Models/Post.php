<?php

namespace AnourValar\LaravelAtom\Tests\Models;

class Post extends \Illuminate\Database\Eloquent\Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'data' => \AnourValar\LaravelAtom\Tests\Mappers\SimpleMapper::class,
    ];
}
