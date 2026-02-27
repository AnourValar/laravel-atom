<?php

namespace AnourValar\LaravelAtom\Tests;

class ServiceTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_normalizeKey()
    {
        $service = new \AnourValar\LaravelAtom\Service(new \AnourValar\LaravelAtom\Registry(), []);

        $this->assertSame('"foo"', $service->normalizeKey('foo'));
        $this->assertSame('{"foo":"bar"}', $service->normalizeKey(['foo' => 'bar']));

        $this->assertSame('foo', $service->normalizeKey('foo', false));
        $this->assertSame(['foo' => 'bar'], $service->normalizeKey(['foo' => 'bar'], false));

        $this->assertSame(
            [ ['foo' => 'bar'] ],
            $service->normalizeKey([['foo' => 'bar']], false)
        );

        $this->assertSame(
            [ ['a' => '1', 'b' => '1', 'c' => '0', 'd' => '0', 'e' => '', 'f' => '3', 'g' => '2.5', 'h' => '', 'i' => '5', 'j' => '5.5'] ],
            $service->normalizeKey([['a' => 1, 'b' => true, 'c' => false, 'd' => 0, 'e' => ' ', 'f' => '3', 'g' => 2.5, 'h' => '', 'i' => 5, 'j' => '5.5']], false)
        );

        $this->assertSame(
            [ ['one'], ['foo' => ['bar' => ['baz' => '123.45']]] ],
            $service->normalizeKey([['one'], ['foo' => ['bar' => ['baz' => 123.45]]]], false)
        );

        $this->assertSame(
            ['', '', '', '1', '0', '2', '3.14', '4.14', '1', '0', []],
            $service->normalizeKey([' ', '', null, 1, 0, 2.0, 3.14, '4.14', true, false, []], false)
        );

        $this->assertSame(
            [['', '', '', '1', '0', '2', '3.14', '4.14', '1', '0', []]],
            $service->normalizeKey([[' ', '', null, 1, 0, 2.0, 3.14, '4.14', true, false, []]], false)
        );

        $this->assertSame(
            [[['', '', '', '1', '0', '2', '3.14', '4.14', '1', '0', []]]],
            $service->normalizeKey([[[' ', '', null, 1, 0, 2.0, 3.14, '4.14', true, false, []]]], false)
        );

        $service->normalizeKey(null, true, true);
        $this->expectException(\RuntimeException::class);
        $service->normalizeKey(null, true, false);
    }
}
