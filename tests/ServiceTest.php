<?php

namespace AnourValar\LaravelAtom\Tests;

class ServiceTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_canonizeArgs()
    {
        $class = new class (new \AnourValar\LaravelAtom\Registry(), []) extends \AnourValar\LaravelAtom\Service {
            public function obtain()
            {
                return $this->canonizeArgs(func_get_args());
            }
        };

        $this->assertSame(
            [ ['foo' => 'bar'] ],
            $class->obtain(['foo' => 'bar'])
        );

        $this->assertSame(
            [ ['a' => '1', 'b' => '1', 'c' => '0', 'd' => '0', 'e' => '', 'f' => '3', 'g' => '2.5', 'h' => '', 'i' => '5', 'j' => '5.5'] ],
            $class->obtain(['a' => 1, 'b' => true, 'c' => false, 'd' => 0, 'e' => ' ', 'f' => '3', 'g' => 2.5, 'h' => '', 'i' => 5, 'j' => '5.5'])
        );

        $this->assertSame(
            [ ['one'], ['foo' => ['bar' => ['baz' => '123.45']]] ],
            $class->obtain(['one'], ['foo' => ['bar' => ['baz' => 123.45]]])
        );
    }
}
