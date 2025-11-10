<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class NumberHelperTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_formatMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertSame('0', $helper->formatMultiple(0));
        $this->assertSame('0.0000000001', $helper->formatMultiple(1));
        $this->assertSame('0.000000001', $helper->formatMultiple(10));
        $this->assertSame('0.00000001', $helper->formatMultiple(100));
        $this->assertSame('0.0000001', $helper->formatMultiple(1000));
        $this->assertSame('0.000001', $helper->formatMultiple(10000));
        $this->assertSame('0.00001', $helper->formatMultiple(100000));
        $this->assertSame('0.0001', $helper->formatMultiple(1000000));
        $this->assertSame('0.001', $helper->formatMultiple(10000000));
        $this->assertSame('0.01', $helper->formatMultiple(100000000));
        $this->assertSame('0.1', $helper->formatMultiple(1000000000));
        $this->assertSame('1', $helper->formatMultiple(10000000000));
        $this->assertSame('10', $helper->formatMultiple(100000000000));
        $this->assertSame('100', $helper->formatMultiple(1000000000000));
        $this->assertSame('1000', $helper->formatMultiple(10000000000000));
        $this->assertSame('10000', $helper->formatMultiple(100000000000000));
        $this->assertSame('100000', $helper->formatMultiple(1000000000000000));

        $this->assertSame('0.0000000014', $helper->formatMultiple(14));
        $this->assertSame('0.000000014', $helper->formatMultiple(140));
        $this->assertSame('0.00000014', $helper->formatMultiple(1400));
        $this->assertSame('0.0000014', $helper->formatMultiple(14000));
        $this->assertSame('0.000014', $helper->formatMultiple(140000));
        $this->assertSame('0.00014', $helper->formatMultiple(1400000));
        $this->assertSame('0.0014', $helper->formatMultiple(14000000));
        $this->assertSame('0.014', $helper->formatMultiple(140000000));
        $this->assertSame('0.14', $helper->formatMultiple(1400000000));
        $this->assertSame('1.4', $helper->formatMultiple(14000000000));
        $this->assertSame('14', $helper->formatMultiple(140000000000));
        $this->assertSame('140', $helper->formatMultiple(1400000000000));
        $this->assertSame('1400', $helper->formatMultiple(14000000000000));
        $this->assertSame('14000', $helper->formatMultiple(140000000000000));
        $this->assertSame('140000', $helper->formatMultiple(1400000000000000));

        $this->assertSame('92233720368547758062132.1321332133', $helper->formatMultiple('922337203685477580621321321332133'));
    }
    /**
     * @return void
     */
    public function test_encodeMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertSame(null, $helper->encodeMultiple(null));
        $this->assertSame(23456789098765432, $helper->encodeMultiple(2345678.9098765432));
        $this->assertSame(23456789098765432, $helper->encodeMultiple('2345678.9098765432'));
    }

    /**
     * @return void
     */
    public function test_decodeMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertSame(null, $helper->decodeMultiple(null));
        $this->assertSame(2345678.9098765432, $helper->decodeMultiple(23456789098765432));
        $this->assertSame(2345678.9098765432, $helper->decodeMultiple('23456789098765432'));
    }
}
