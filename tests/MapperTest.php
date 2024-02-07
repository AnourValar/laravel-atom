<?php

namespace AnourValar\LaravelAtom\Tests;

use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\MappingSnakeCase;
use AnourValar\LaravelAtom\Tests\Mappers\SimpleMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ComplexMapper;
use AnourValar\LaravelAtom\Tests\Mappers\NestedMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ModeMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ArrayOfMapper;
use AnourValar\LaravelAtom\Tests\Mappers\ExcludeMapper;
use AnourValar\LaravelAtom\Tests\Models\Post;

class MapperTest extends \Orchestra\Testbench\TestCase
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

        $this->assertSame(['a' => '1'], $mapper->only('a'));
        $this->assertSame(['a' => '1', 'b' => null], $mapper->only(['a', 'b']));

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

        $this->expectException(\RuntimeException::class);
        try {
            SimpleMapper::from(['a' => 1, 'b' => '2', 'eee' => '3']);
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('eee', $e->getMessage());
            throw $e;
        }
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
        $mapper = ModeMapper::from(['user_id' => '1', 'manager_id' => '2', 'ids' => [1]]);

        $this->assertSame(1, $mapper['userId']);
        $this->assertSame(1, $mapper->userId);

        $this->assertSame(
            ['user_id' => 1, 'manager_id' => 2, 'ids' => [1]],
            $mapper->toArray()
        );

        $this->assertSame(
            ['user_id' => 1, 'manager_id' => 3, 'ids' => []],
            ModeMapper::from(['user_id' => '1', 'ids' => []])->toArray()
        );

        $this->assertSame(
            ['user_id' => 1, 'manager_id' => 3, 'ids' => [], 'name' => 'foo'],
            ModeMapper::from(['user_id' => '1', 'ids' => null, 'name' => ' foo '])->toArray()
        );
    }

    /**
     * @return void
     */
    public function test_arrayOf_exclude()
    {
        $mapper = ArrayOfMapper::from([
            'excludes' => [
                ['a' => 1, 'b' => 2],
                ExcludeMapper::from(['a' => 3, 'b' => 4]),
                ['a' => 5, 'b' => 6],
            ],
        ]);

        $this->assertSame('1', $mapper->excludes[0]['a']);
        $this->assertSame('2', $mapper->excludes[0]['b']);
        $this->assertSame(['b' => '2'], $mapper->excludes[0]->toArray());
        $this->assertInstanceOf(ExcludeMapper::class, $mapper->excludes[0]);
        $this->assertInstanceOf(ExcludeMapper::class, $mapper->excludes[1]);
        $this->assertInstanceOf(ExcludeMapper::class, $mapper->excludes[2]);

        $this->assertSame(
            [
                'excludes' => [
                    ['b' => '2'],
                    ['b' => '4'],
                    ['b' => '6'],
                ],
            ],
            $mapper->toArray()
        );
    }

    /**
     * @return void
     */
    public function test_model()
    {
        $post = new Post();

        $post->forceFill(['data' => ['a' => 1, 'b' => 2]]);
        $this->assertInstanceOf(SimpleMapper::class, $post->data);
        $this->assertSame(['a' => '1', 'b' => 2, 'c' => null, 'd' => 1], $post->data->toArray());
        $this->assertSame('1', $post->data->a);
        $this->assertSame('1', $post->data['a']);
        $this->assertSame(json_encode(['a' => '1', 'b' => 2, 'c' => null, 'd' => 1]), $post->getAttributes()['data']);

        $post->forceFill(['data' => null]);
        $this->assertNull($post->data);

        $post->forceFill(['data' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => '4']]);
        $this->assertInstanceOf(SimpleMapper::class, $post->data);
        $this->assertSame(['a' => '1', 'b' => 2, 'c' => '3', 'd' => 4], $post->data->toArray());
    }
}
