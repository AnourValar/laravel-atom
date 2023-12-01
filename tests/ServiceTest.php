<?php

namespace AnourValar\LaravelAtom\Tests;

class ServiceTest extends \PHPUnit\Framework\TestCase
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
            [ ['a' => '1', 'b' => true, 'c' => false, 'd' => '0', 'e' => ' ', 'f' => '3', 'g' => '2.5', 'h' => ''] ],
            $class->obtain(['a' => 1, 'b' => true, 'c' => false, 'd' => 0, 'e' => ' ', 'f' => '3', 'g' => 2.5, 'h' => ''])
        );

        $this->assertSame(
            [ ['one'], ['foo' => ['bar' => ['baz' => '123.45']]] ],
            $class->obtain(['one'], ['foo' => ['bar' => ['baz' => 123.45]]])
        );
    }
}
