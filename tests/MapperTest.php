<?php

namespace AnourValar\LaravelAtom\Tests;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Tests\Mappers\SimpleMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ComplexMapper;
use AnourValar\LaravelAtom\Tests\Mappers\NestedMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ModeMapper;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function test_simple()
    {
        $mapper = SimpleMapper::from([
            'a' => 1,
            'b' => null,
            'd' => '2',
        ]);

        $this->assertSame('1', $mapper->a);
        $this->assertSame(null, $mapper->b);
        $this->assertSame(null, $mapper->c);
        $this->assertSame(2, $mapper->d);

        $this->assertSame('1', $mapper['a']);
        $this->assertSame(null, $mapper['b']);
        $this->assertSame(null, $mapper['c']);
        $this->assertSame(2, $mapper['d']);

        $this->assertSame(
            [
                'a' => '1',
                'b' => null,
                'c' => null,
                'd' => 2,
            ],
            $mapper->toArray()
        );

        $this->assertSame(
            json_encode([
                'a' => '1',
                'b' => null,
                'c' => null,
                'd' => 2,
            ]),
            json_encode($mapper->toArray())
        );

        $this->assertSame(
            [
                'a' => '1',
                'b' => '2',
                'c' => '3',
                'd' => 4,
            ],
            (new SimpleMapper(1, '2', '3', '4'))->toArray()
        );
    }

    /**
     * @return void
     */
    public function test_complex()
    {
        $mapper = ComplexMapper::from([
            'userId' => '1',
            'boss_id' => '2',
            'mapper1' => NestedMapper::from(['a' => 'foo1']),
            'mapper2' => ['a' => 'foo2'],
            'mappers4' => [
                NestedMapper::from(['a' => 'foo3']),
                ['a' => 'foo4'],
            ],
        ]);

        $this->assertSame(2, $mapper->managerId);
        $this->assertSame(2, $mapper->managerId);

        $this->assertSame(
            [
                'userId' => 1,
                'boss_id' => 2,
                'mapper1' => ['a' => 'foo1'],
                'mapper2' => ['a' => 'foo2'],
                'mapper3' => null,
                'mappers4' => [
                    ['a' => 'foo3'],
                    ['a' => 'foo4'],
                ],
            ],
            $mapper->toArray()
        );
    }

    /**
     * @return void
     */
    public function test_nested()
    {
        $this->assertSame(
            ['a' => '1'],
            NestedMapper::from(['a' => 1])->toArray()
        );

        $this->assertSame(
            [],
            NestedMapper::from([])->toArray()
        );
    }

    /**
     * @return void
     */
    public function test_mode()
    {
        $mapper = ModeMapper::from(['user_id' => '1', 'manager_id' => '2']);

        $this->assertSame(1, $mapper['userId']);
        $this->assertSame(1, $mapper->userId);

        $this->assertSame(
            ['user_id' => 1, 'manager_id' => 2],
            $mapper->toArray()
        );

        $this->assertSame(
            ['user_id' => 1, 'manager_id' => 3],
            ModeMapper::from(['user_id' => '1'])->toArray()
        );
    }
}
