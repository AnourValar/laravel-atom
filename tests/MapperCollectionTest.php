<?php

namespace AnourValar\LaravelAtom\Tests;

use AnourValar\LaravelAtom\Tests\Mappers\MapperCollectionSimple;
use AnourValar\LaravelAtom\Tests\Mappers\SimpleMapper;

class MapperCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideSimpleData
     * @return void
     */
    public function test_simple(array $data)
    {
        $mapper = MapperCollectionSimple::from($data);

        $index = 0;
        foreach ($mapper as $item) {

            if ($index == 0) {
                $this->assertSame(['a' => 'a1', 'b' => 'b1', 'c' => null, 'd' => 1], $item->toArray());
            }

            if ($index == 1) {
                $this->assertSame(['a' => 'a2', 'b' => 'b2', 'c' => null, 'd' => 1], $item->toArray());
            }

            if ($index == 2) {
                $this->assertSame(['a' => 'a3', 'b' => 'b3', 'c' => null, 'd' => 1], $item->toArray());
            }

            $index++;
        }

        $keys = array_keys($data);
        $this->assertSame(
            [
                $keys[0] => ['a' => 'a1', 'b' => 'b1', 'c' => null, 'd' => 1],
                $keys[1] => ['a' => 'a2', 'b' => 'b2', 'c' => null, 'd' => 1],
                $keys[2] => ['a' => 'a3', 'b' => 'b3', 'c' => null, 'd' => 1],
            ],
            $mapper->toArray()
        );

        $this->assertSame(['a' => 'a1', 'b' => 'b1', 'c' => null, 'd' => 1], $mapper[$keys[0]]->toArray());
        $this->assertSame(['a' => 'a2', 'b' => 'b2', 'c' => null, 'd' => 1], $mapper[$keys[1]]->toArray());
        $this->assertSame(['a' => 'a3', 'b' => 'b3', 'c' => null, 'd' => 1], $mapper[$keys[2]]->toArray());
    }

    public static function provideSimpleData()
    {
        return [
            // list
            [
                [
                    ['a' => 'a1', 'b' => 'b1'],
                    SimpleMapper::from(['a' => 'a2', 'b' => 'b2']),
                    ['a' => 'a3', 'b' => 'b3'],
                ],
            ],
            // assoc
            [
                [
                    'foo' => ['a' => 'a1', 'b' => 'b1'],
                    'bar' => SimpleMapper::from(['a' => 'a2', 'b' => 'b2']),
                    'baz' => ['a' => 'a3', 'b' => 'b3'],
                ],
            ],
        ];
    }
}
