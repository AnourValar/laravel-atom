<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class ArrayHelperTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_applyDataToSchema_1()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);
        $schema = [
            '1' => '%foo%',
            '2' => 'a %foo% b',

            '3' => '$foo$',
            '4' => 'a $foo$ b',

            '5' => [],
            '6' => '',
            '7' => null,
        ];

        $this->assertSame(
            [
                '1' => 'FOO',
                '2' => 'a FOO b',

                '3' => 'FOO',
                '4' => 'a FOO b',

                '5' => [],
                '6' => '',
                '7' => null,
            ],
            $service->applyDataToSchema($schema, ['foo' => 'FOO'])
        );

        $this->assertSame(
            [
                '1' => '',
                '2' => 'a  b',

                //'3' => '',
                //'4' => 'a  b',

                '5' => [],
                '6' => '',
                '7' => null,
            ],
            $service->applyDataToSchema($schema, [])
        );
    }

    /**
     * @return void
     */
    public function test_applyDataToSchema_2()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);
        $schema = [
            '1' => ['a' => '$foo$'],
            '2' => ['a' => ['b' => '$foo$']],

            '3' => ['a' => '$foo$', 'b' => ''],
            '4' => ['a' => ['b' => '$foo$'], 'c' => ''],
            '5' => ['a' => ['b' => '$foo$', 'c' => '']],
        ];

        $this->assertSame(
            [
                '3' => ['b' => ''],
                '4' => ['c' => ''],
                '5' => ['a' => ['c' => '']],
            ],
            $service->applyDataToSchema($schema, ['foo' => null])
        );
    }

    /**
     * @return void
     */
    public function test_applyDataToSchema_3()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);
        $schema = [
            '1' => '%foo% %a.b.c% %b@r%',
            '2' => ['%foo% %a.b.c%'],

            '3' => [['%a.b.c% %b@r%']],
        ];

        $this->assertSame(
            [
                '1' => 'FOO ABC BAR',
                '2' => ['FOO ABC'],

                '3' => [['ABC BAR']],
            ],
            $service->applyDataToSchema($schema, ['foo' => 'FOO', 'b@r' => 'BAR', 'a' => ['b' => ['c' => 'ABC']]])
        );

        $this->assertSame(
            [
                '1' => '  ',
                '2' => [' '],

                '3' => [[' ']],
            ],
            $service->applyDataToSchema($schema, [])
        );
    }
}
