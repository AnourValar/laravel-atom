<?php

namespace AnourValar\LaravelAtom\Tests;

use AnourValar\LaravelAtom\Tests\Mappers\MapperCollectionSimple;
use AnourValar\LaravelAtom\Tests\Mappers\SimpleMapper;
use AnourValar\LaravelAtom\Tests\Mappers\MapperCollectionJsonb;
use AnourValar\LaravelAtom\Tests\Mappers\JsonMapper;
use AnourValar\LaravelAtom\Tests\Models\Post;

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

    /**
     * @return void
     */
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

    /**
     * @return void
     * @psalm-suppress NullReference
     */
    public function test_model()
    {
        $post = new Post();

        $post->forceFill(['data2' => [['a' => 1, 'b' => 2]]]);
        $this->assertSame(json_encode([['a' => '1', 'b' => 2, 'c' => null, 'd' => 1]]), $post->getAttributes()['data2']);
        $this->assertInstanceOf(SimpleMapper::class, $post->data2[0]);
        $this->assertSame([['a' => '1', 'b' => 2, 'c' => null, 'd' => 1]], $post->data2->toArray());
        $this->assertSame('1', $post->data2[0]->a);
        $this->assertSame('1', $post->data2[0]['a']);

        $post->forceFill(['data2' => null]);
        $this->assertNull($post->getAttributes()['data2']);
        $this->assertNull($post->data2);

        $post->forceFill(['data2' => [['a' => 1, 'b' => 2, 'c' => 3, 'd' => '4']]]);
        $this->assertInstanceOf(SimpleMapper::class, $post->data2[0]);
        $this->assertSame([['a' => '1', 'b' => 2, 'c' => '3', 'd' => 4]], $post->data2->toArray());

        $this->assertSame(['data2' => [['a' => '1', 'b' => 2, 'c' => '3', 'd' => 4]]], $post->toArray());
        $this->assertSame(['data2' => json_encode([['a' => '1', 'b' => 2, 'c' => '3', 'd' => 4]])], $post->getAttributes());
    }

    /**
     * @return void
     */
    public function test_jsonb()
    {
        $this->assertSame(
            [ ['a' => '2', 'aa' => '1'], ['a' => '4', 'aa' => '3'] ],
            MapperCollectionJsonb::from([ ['aa' => 1, 'a' => 2], JsonMapper::from(['aa' => 3, 'a' => 4]) ])->toArray()
        );
    }
}
