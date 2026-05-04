<?php

namespace AnourValar\LaravelAtom\Tests;

class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function test_toArray()
    {
        $class = new class () extends \AnourValar\LaravelAtom\Repository
        {
            public function toArray($data)
            {
                return parent::toArray(...func_get_args());
            }
        };

        $this->assertSame('foo', $class->toArray('foo'));

        $this->assertSame(
            [
                ['a' => 1, 'b' => 2, 'c' => [3]],
                ['a' => 4, 'b' => 5, 'c' => [6]],
            ],
            $class->toArray(
                [
                    (object) ['a' => 1, 'b' => 2, 'c' => [3]],
                    (object) ['a' => 4, 'b' => 5, 'c' => (object) [6]],
                ]
            )
        );

        $this->assertSame(
            [
                ['a' => ['foo' => 'bar']],
                ['a' => ['baz' => 'foobar']],
            ],
            $class->toArray(
                (object) [
                    ['a' => (object) ['foo' => 'bar']],
                    (object) ['a' => ['baz' => 'foobar']],
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function test_gap()
    {
        $class = new class () extends \AnourValar\LaravelAtom\Repository
        {
            public function gap(array $data, \Carbon\CarbonInterface $dateFrom, \Carbon\CarbonInterface $dateTo, array|callable $defaults, string $format = 'Y-m-d'): array
            {
                return parent::gap(...func_get_args());
            }
        };

        $this->assertSame(
            [
                    ['date' => '2025-04-15', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-15', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-16', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-16', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-17', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-17', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-18', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-18', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-19', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-19', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-20', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-20', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-21', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-21', 'status' => 'bar', 'qty' => 0],
            ],
            $class->gap(
                [],
                \Carbon\Carbon::parse('2025-04-15'),
                \Carbon\Carbon::parse('2025-04-21'),
                [
                    ['date' => null, 'status' => 'foo', 'qty' => 0],
                    ['date' => null, 'status' => 'bar', 'qty' => 0],
                ]
            )
        );

        $this->assertSame(
            [
                    ['date' => '2025-04-15', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-15', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-16', 'status' => 'foo', 'qty' => 1],
                    ['date' => '2025-04-16', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-17', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-17', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-18', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-18', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-19', 'status' => 'foo', 'qty' => 2],
                    ['date' => '2025-04-19', 'status' => 'bar', 'qty' => 3],

                    ['date' => '2025-04-20', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-20', 'status' => 'bar', 'qty' => 4],

                    ['date' => '2025-04-21', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-21', 'status' => 'bar', 'qty' => 0],
            ],
            $class->gap(
                [
                    ['date' => '2025-04-16', 'status' => 'foo', 'qty' => 1],
                    ['date' => '2025-04-19', 'status' => 'foo', 'qty' => 2],
                    ['date' => '2025-04-19', 'status' => 'bar', 'qty' => 3],
                    ['date' => '2025-04-20', 'status' => 'bar', 'qty' => 4],
                ],
                \Carbon\Carbon::parse('2025-04-15'),
                \Carbon\Carbon::parse('2025-04-21'),
                [
                    ['date' => null, 'status' => 'foo', 'qty' => 0],
                    ['date' => null, 'status' => 'bar', 'qty' => 0],
                ]
            )
        );

        $this->assertSame(
            [
                    ['date' => '2025-04-15', 'status' => 'foo', 'qty' => 1],
                    ['date' => '2025-04-15', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-16', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-16', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-17', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-17', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-18', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-18', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-19', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-19', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-20', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-20', 'status' => 'bar', 'qty' => 0],

                    ['date' => '2025-04-21', 'status' => 'foo', 'qty' => 0],
                    ['date' => '2025-04-21', 'status' => 'bar', 'qty' => 2],
            ],
            $class->gap(
                [
                    ['date' => '2025-04-15', 'status' => 'foo', 'qty' => 1],
                    ['date' => '2025-04-21', 'status' => 'bar', 'qty' => 2],
                ],
                \Carbon\Carbon::parse('2025-04-15'),
                \Carbon\Carbon::parse('2025-04-21'),
                [
                    ['date' => null, 'status' => 'foo', 'qty' => 0],
                    ['date' => null, 'status' => 'bar', 'qty' => 0],
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function test_conditions()
    {
        $class = new class () extends \AnourValar\LaravelAtom\Repository
        {
            public function proxy(): array
            {
                return parent::conditions(...func_get_args());
            }
        };

        $this->assertSame(
            ['sql' => '', 'bindings' => []],
            $class->proxy()
        );

        $this->assertSame(
            ['sql' => '', 'bindings' => []],
            $class->proxy([0 => fn () => 1])
        );

        $this->assertSame(
            ['sql' => '', 'bindings' => []],
            $class->proxy([0 => fn () => 1], [false => fn () => 2])
        );

        $this->assertSame(
            ['sql' => '', 'bindings' => []],
            $class->proxy([0 => fn () => 1], [false => fn () => 2], ['' => fn () => 3])
        );


        $this->assertSame(
            ['sql' => 'AND a = :a', 'bindings' => ['a' => 'foo']],
            $class->proxy([1 => fn () => ['a = :a', ['a' => 'foo']]])
        );

        $this->assertSame(
            ['sql' => 'AND a IN (:a, :b, :c)', 'bindings' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']],
            $class->proxy([1 => fn () => ['a IN (:a, :b, :c)', ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']]])
        );



        $this->assertSame(
            ['sql' => 'AND a = 1 AND c = 3', 'bindings' => []],
            $class->proxy([1 => fn () => 'a = 1'], [0 => fn () => 'b = 2'], [true => fn () => ['c = 3']])
        );

        $this->assertSame(
            ['sql' => 'AND a = 1 AND c = 3 AND e = 5', 'bindings' => []],
            $class->proxy([1 => fn () => 'a = 1'], [0 => fn () => 'b = 2'], [true => fn () => 'c = 3'], [false => fn () => 'd = 4'], ['1' => fn () => 'e = 5'])
        );
    }
}
