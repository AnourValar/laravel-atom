<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class ArrayHelperTest extends \AnourValar\LaravelAtom\Tests\AbstractSuite
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

    /**
     * @return void
     */
    public function test_jsonPretty()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);

        $this->assertSame(
            "{\n    \"foo\": \"bar\",\n    \"baz\": [\n        1,\n        2\n    ]\n}",
            $service->jsonPretty(['foo' => 'bar', 'baz' => [1, 2]])
        );

        $this->assertSame(
            '{ "foo": "bar", "baz": [ 1, 2 ] }',
            $service->jsonPretty(['foo' => 'bar', 'baz' => [1, 2]], false)
        );

        // unescaped unicode & slashes
        $this->assertSame('{ "url": "http://example.org", "name": "Иван" }', $service->jsonPretty(['url' => 'http://example.org', 'name' => 'Иван'], false));
    }

    /**
     * @return void
     */
    public function test_export()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);

        $this->assertSame("    'a', 'b',", $service->export(['a', 'b']));
        $this->assertSame("    'a' => 1, 'b' => 'two',", $service->export(['a' => 1, 'b' => 'two']));

        $this->assertSame(
            "    'a' => 1,\n    'b' => ['c' => 2, 'd' => 3],",
            $service->export(['a' => 1, 'b' => ['c' => 2, 'd' => 3]])
        );

        // inline disabled
        $this->assertSame(
            "    'a' => 1,\n    'b' => 'two',",
            $service->export(['a' => 1, 'b' => 'two'], false)
        );

        // null value & quote escaping
        $this->assertSame("    'a' => null, 'b' => 'a\\'b',", $service->export(['a' => null, 'b' => "a'b"]));
    }

    /**
     * @return void
     */
    public function test_getStructureDiff()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);

        // identical
        $this->assertNull($service->getStructureDiff(['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4], []));

        // extra key in the first array
        $this->assertSame('b', $service->getStructureDiff(['a' => 1, 'b' => 2], ['a' => 1], []));

        // extra key only in the second array is ignored
        $this->assertNull($service->getStructureDiff(['a' => 1], ['a' => 1, 'b' => 2], []));

        // exceptions
        $this->assertNull($service->getStructureDiff(['a' => 1, 'b' => 2], ['a' => 1], ['b']));

        // integer keys are ignored
        $this->assertNull($service->getStructureDiff([0 => 'x', 1 => 'y'], [0 => 'x'], []));

        // nested
        $this->assertSame('a.c', $service->getStructureDiff(['a' => ['b' => 1, 'c' => 2]], ['a' => ['b' => 1]], []));
    }

    /**
     * @return void
     */
    public function test_getTypeDiff()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);

        // identical types
        $this->assertNull($service->getTypeDiff(['a' => 1, 'b' => 'x'], ['a' => 2, 'b' => 'y']));

        // numeric strings count as numeric
        $this->assertNull($service->getTypeDiff(['a' => '1'], ['a' => 1]));

        // numeric mismatch
        $this->assertSame('a', $service->getTypeDiff(['a' => 'x'], ['a' => 1]));

        // bool mismatch
        $this->assertSame('a', $service->getTypeDiff(['a' => 1], ['a' => true]));

        // array mismatch
        $this->assertSame('a', $service->getTypeDiff(['a' => 1], ['a' => [1]]));

        // missing keys in the first array are ignored
        $this->assertNull($service->getTypeDiff(['a' => 1], ['a' => 1, 'b' => true]));

        // nested
        $this->assertSame('a.b', $service->getTypeDiff(['a' => ['b' => 'x']], ['a' => ['b' => 1]]));
    }

    /**
     * @return void
     */
    public function test_mergeRecursive()
    {
        $service = \App::make(\AnourValar\LaravelAtom\Helpers\ArrayHelper::class);

        // lists are concatenated (unlike array_replace_recursive)
        $this->assertSame([['a', 'b']], $service->mergeRecursive(['0' => ['a']], ['0' => ['b']]));

        // scalars are replaced (unlike array_merge_recursive)
        $this->assertSame(['a' => 2], $service->mergeRecursive(['a' => 1], ['a' => 2]));

        // new keys are added
        $this->assertSame(['a' => 1, 'b' => 2], $service->mergeRecursive(['a' => 1], ['b' => 2]));

        // assoc arrays are merged recursively
        $this->assertSame(
            ['a' => ['x' => 1, 'y' => 2]],
            $service->mergeRecursive(['a' => ['x' => 1]], ['a' => ['y' => 2]])
        );

        // mix
        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 3, 'd' => 4], 'e' => [1, 2, 3]],
            $service->mergeRecursive(
                ['a' => 0, 'b' => ['c' => 3], 'e' => [1]],
                ['a' => 1, 'b' => ['d' => 4], 'e' => [2, 3]]
            )
        );
    }
}
