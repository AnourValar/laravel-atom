<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class NumberHelperTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_encodeMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertSame(null, $helper->encodeMultiple(null));
        $this->assertSame('0', $helper->encodeMultiple(''));

        $this->assertSame('0', $helper->encodeMultiple('0.00000000001'));
        $this->assertSame('0', $helper->encodeMultiple('0.00000000009'));
        $this->assertSame('1', $helper->encodeMultiple('0.00000000012'));
        $this->assertSame('12', $helper->encodeMultiple('0.00000000123'));

        $this->assertSame('1', $helper->encodeMultiple('0.0000000001'));
        $this->assertSame('10', $helper->encodeMultiple('0.000000001'));
        $this->assertSame('100', $helper->encodeMultiple('0.00000001'));
        $this->assertSame('1000', $helper->encodeMultiple('0.0000001'));
        $this->assertSame('10000', $helper->encodeMultiple('0.000001'));
        $this->assertSame('100000', $helper->encodeMultiple('0.00001'));
        $this->assertSame('1000000', $helper->encodeMultiple('0.0001'));
        $this->assertSame('10000000', $helper->encodeMultiple('0.001'));
        $this->assertSame('100000000', $helper->encodeMultiple('0.01'));
        $this->assertSame('1000000000', $helper->encodeMultiple('0.1'));
        $this->assertSame('10000000000', $helper->encodeMultiple('1'));
        $this->assertSame('100000000000', $helper->encodeMultiple('10'));
        $this->assertSame('1000000000000', $helper->encodeMultiple('100'));
        $this->assertSame('10000000000000', $helper->encodeMultiple('1000'));
        $this->assertSame('100000000000000', $helper->encodeMultiple('10000'));
        $this->assertSame('1000000000000000', $helper->encodeMultiple('100000'));
        $this->assertSame('10000000000000000', $helper->encodeMultiple('1000000'));
        $this->assertSame('100000000000000000', $helper->encodeMultiple('10000000'));
        $this->assertSame('1000000000000000000', $helper->encodeMultiple('100000000'));
        $this->assertSame('10000000000000000000', $helper->encodeMultiple('1000000000'));
        $this->assertSame('100000000000000000000', $helper->encodeMultiple('10000000000'));
        $this->assertSame('1000000000000000000000', $helper->encodeMultiple('100000000000'));
        $this->assertSame('10000000000000000000000', $helper->encodeMultiple('1000000000000'));

        $this->assertSame('12', $helper->encodeMultiple('0.0000000012'));
        $this->assertSame('123', $helper->encodeMultiple('0.0000000123'));
        $this->assertSame('1234', $helper->encodeMultiple('0.0000001234'));
        $this->assertSame('12345', $helper->encodeMultiple('0.0000012345'));
        $this->assertSame('123456', $helper->encodeMultiple('0.0000123456'));
        $this->assertSame('1234567', $helper->encodeMultiple('0.0001234567'));
        $this->assertSame('12345678', $helper->encodeMultiple('0.0012345678'));
        $this->assertSame('123456789', $helper->encodeMultiple('0.0123456789'));
        $this->assertSame('1234567890', $helper->encodeMultiple('0.1234567890'));
        $this->assertSame('12345678901', $helper->encodeMultiple('1.2345678901'));
        $this->assertSame('123456789012', $helper->encodeMultiple('12.3456789012'));
        $this->assertSame('1234567890123', $helper->encodeMultiple('123.4567890123'));
        $this->assertSame('12345678901234', $helper->encodeMultiple('1234.5678901234'));
        $this->assertSame('123456789012345', $helper->encodeMultiple('12345.6789012345'));
        $this->assertSame('1234567890123456', $helper->encodeMultiple('123456.7890123456'));
        $this->assertSame('12345678901234567', $helper->encodeMultiple('1234567.8901234567'));
        $this->assertSame('123456789012345678', $helper->encodeMultiple('12345678.9012345678'));
        $this->assertSame('1234567890123456789', $helper->encodeMultiple('123456789.0123456789'));
        $this->assertSame('12345678901234567890', $helper->encodeMultiple('1234567890.1234567890'));

        $this->assertSame('23456789098765000', $helper->encodeMultiple(2345678.9098765));
        $this->assertSame('23456789098765000', $helper->encodeMultiple(2345678.9098765000));
        $this->assertSame('23456789098765432', $helper->encodeMultiple('2345678.9098765432'));
        $this->assertSame('-23456789098765432', $helper->encodeMultiple('-2345678.9098765432'));
    }

    /**
     * @return void
     */
    public function test_decodeMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertNull($helper->decodeMultiple(null));
        $this->assertSame('0', $helper->decodeMultiple(''));

        $this->assertSame('0', $helper->decodeMultiple(0));
        $this->assertSame('0.0000000001', $helper->decodeMultiple(1));
        $this->assertSame('0.000000001', $helper->decodeMultiple(10));
        $this->assertSame('0.00000001', $helper->decodeMultiple(100));
        $this->assertSame('0.0000001', $helper->decodeMultiple(1000));
        $this->assertSame('0.000001', $helper->decodeMultiple(10000));
        $this->assertSame('0.00001', $helper->decodeMultiple(100000));
        $this->assertSame('0.0001', $helper->decodeMultiple(1000000));
        $this->assertSame('0.001', $helper->decodeMultiple(10000000));
        $this->assertSame('0.01', $helper->decodeMultiple(100000000));
        $this->assertSame('0.1', $helper->decodeMultiple(1000000000));
        $this->assertSame('1', $helper->decodeMultiple(10000000000));
        $this->assertSame('10', $helper->decodeMultiple(100000000000));
        $this->assertSame('100', $helper->decodeMultiple(1000000000000));
        $this->assertSame('1000', $helper->decodeMultiple(10000000000000));
        $this->assertSame('10000', $helper->decodeMultiple(100000000000000));
        $this->assertSame('100000', $helper->decodeMultiple(1000000000000000));
        $this->assertSame('1000000', $helper->decodeMultiple('10000000000000000'));
        $this->assertSame('10000000', $helper->decodeMultiple('100000000000000000'));
        $this->assertSame('100000000', $helper->decodeMultiple('1000000000000000000'));
        $this->assertSame('1000000000', $helper->decodeMultiple('10000000000000000000'));
        $this->assertSame('10000000000', $helper->decodeMultiple('100000000000000000000'));

        $this->assertSame('0.0000000012', $helper->decodeMultiple(12));
        $this->assertSame('0.0000000123', $helper->decodeMultiple(123));
        $this->assertSame('0.0000001234', $helper->decodeMultiple(1234));
        $this->assertSame('0.0000012345', $helper->decodeMultiple(12345));
        $this->assertSame('0.0000123456', $helper->decodeMultiple(123456));
        $this->assertSame('0.0001234567', $helper->decodeMultiple(1234567));
        $this->assertSame('0.0012345678', $helper->decodeMultiple(12345678));
        $this->assertSame('0.0123456789', $helper->decodeMultiple(123456789));
        $this->assertSame('0.123456789', $helper->decodeMultiple(1234567890));
        $this->assertSame('1.2345678901', $helper->decodeMultiple(12345678901));
        $this->assertSame('12.3456789012', $helper->decodeMultiple(123456789012));
        $this->assertSame('123.4567890123', $helper->decodeMultiple(1234567890123));
        $this->assertSame('1234.5678901234', $helper->decodeMultiple(12345678901234));
        $this->assertSame('12345.6789012345', $helper->decodeMultiple('123456789012345'));
        $this->assertSame('123456.7890123456', $helper->decodeMultiple('1234567890123456'));
        $this->assertSame('1234567.8901234567', $helper->decodeMultiple('12345678901234567'));
        $this->assertSame('12345678.9012345678', $helper->decodeMultiple('123456789012345678'));
        $this->assertSame('123456789.0123456789', $helper->decodeMultiple('1234567890123456789'));
        $this->assertSame('1234567890.123456789', $helper->decodeMultiple('12345678901234567890'));
        $this->assertSame('12345678901.2345678901', $helper->decodeMultiple('123456789012345678901'));

        $this->assertSame('2345678.9098765432', $helper->decodeMultiple(23456789098765432));
        $this->assertSame('2345678.9098765432', $helper->decodeMultiple('23456789098765432'));
        $this->assertSame('92233720368547758062132.1321332133', $helper->decodeMultiple('922337203685477580621321321332133'));
        $this->assertSame('-92233720368547758062132.1321332133', $helper->decodeMultiple('-922337203685477580621321321332133'));

        $this->assertSame('0.00001', $helper->decodeMultiple(100000.0));
        $this->assertSame('0.00001', $helper->decodeMultiple(100000.9));
    }

    /**
     * @return void
     */
    public function test_formatMultiple()
    {
        config(['atom.number.multiple' => 10000000000]);
        config(['app.locale' => 'en']);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertNull($helper->formatMultiple(null));
        $this->assertSame('0', $helper->formatMultiple(''));
        $this->assertSame('1,000.0000000000', $helper->formatMultiple(10000000000000, 'en', false));

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
        $this->assertSame('1,000', $helper->formatMultiple(10000000000000));
        $this->assertSame('10,000', $helper->formatMultiple(100000000000000));
        $this->assertSame('100,000', $helper->formatMultiple(1000000000000000));
        $this->assertSame('1,000,000', $helper->formatMultiple('10000000000000000'));
        $this->assertSame('10,000,000', $helper->formatMultiple('100000000000000000'));
        $this->assertSame('100,000,000', $helper->formatMultiple('1000000000000000000'));
        $this->assertSame('1,000,000,000', $helper->formatMultiple('10000000000000000000'));
        $this->assertSame('10,000,000,000', $helper->formatMultiple('100000000000000000000'));

        $this->assertSame('0.0000000012', $helper->formatMultiple(12));
        $this->assertSame('0.0000000123', $helper->formatMultiple(123));
        $this->assertSame('0.0000001234', $helper->formatMultiple(1234));
        $this->assertSame('0.0000012345', $helper->formatMultiple(12345));
        $this->assertSame('0.0000123456', $helper->formatMultiple(123456));
        $this->assertSame('0.0001234567', $helper->formatMultiple(1234567));
        $this->assertSame('0.0012345678', $helper->formatMultiple(12345678));
        $this->assertSame('0.0123456789', $helper->formatMultiple(123456789));
        $this->assertSame('0.123456789', $helper->formatMultiple(1234567890));
        $this->assertSame('1.2345678901', $helper->formatMultiple(12345678901));
        $this->assertSame('12.3456789012', $helper->formatMultiple(123456789012));
        $this->assertSame('123.4567890123', $helper->formatMultiple(1234567890123));
        $this->assertSame('1,234.5678901234', $helper->formatMultiple(12345678901234));
        $this->assertSame('12,345.6789012345', $helper->formatMultiple('123456789012345'));
        $this->assertSame('123,456.7890123456', $helper->formatMultiple('1234567890123456'));
        $this->assertSame('1,234,567.8901234567', $helper->formatMultiple('12345678901234567'));
        $this->assertSame('12,345,678.9012345678', $helper->formatMultiple('123456789012345678'));
        $this->assertSame('123,456,789.0123456789', $helper->formatMultiple('1234567890123456789'));
        $this->assertSame('1,234,567,890.123456789', $helper->formatMultiple('12345678901234567890'));
        $this->assertSame('12,345,678,901.2345678901', $helper->formatMultiple('123456789012345678901'));

        $this->assertSame('2,345,678.9098765432', $helper->formatMultiple(23456789098765432));
        $this->assertSame('2,345,678.9098765432', $helper->formatMultiple('23456789098765432'));
        $this->assertSame('92,233,720,368,547,758,062,132.1321332133', $helper->formatMultiple('922337203685477580621321321332133'));
        $this->assertSame('-92,233,720,368,547,758,062,132.1321332133', $helper->formatMultiple('-922337203685477580621321321332133'));
    }

    /**
     * @return void
     */
    public function test_formatNumber()
    {
        config(['app.locale' => 'en']);
        $helper = new \AnourValar\LaravelAtom\Helpers\NumberHelper();

        $this->assertNull($helper->formatNumber(null));
        $this->assertSame('0', $helper->formatNumber(''));
        $this->assertSame('1,000', $helper->formatNumber(1000, 0, null, false));
        $this->assertSame('1,000.0', $helper->formatNumber(1000, 1, null, false));
        $this->assertSame('1,000.00', $helper->formatNumber(1000, 2, null, false));

        $this->assertSame('0', $helper->formatNumber(0));
        $this->assertSame('0', $helper->formatNumber(0.0));
        $this->assertSame('0', $helper->formatNumber('0.00'));

        $this->assertSame('1', $helper->formatNumber(1));
        $this->assertSame('1.2', $helper->formatNumber(1.2));
        $this->assertSame('1.23', $helper->formatNumber('1.23'));
        $this->assertSame('1.23', $helper->formatNumber('1.234'));
        $this->assertSame('1.23', $helper->formatNumber('1.2345'));
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \AnourValar\LaravelAtom\Providers\LaravelAtomServiceProvider::class,
        ];
    }
}
