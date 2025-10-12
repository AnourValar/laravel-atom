<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class StringHelperTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_encrypt_decrypt()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();
        $key1 = base64_encode(random_bytes(32));
        $key2 = 'vv9aFNMrTiYbg3o80inocFJrmAi4kw84j6y/JXA/bms=';

        $this->assertSame('foo', $helper->decrypt($helper->encrypt('foo', $key1), $key1));
        $this->assertSame(['foo' => 'bar'], $helper->decrypt($helper->encrypt(['foo' => 'bar'], $key2), $key2));
        $this->assertSame(2, $helper->decrypt($helper->encrypt(2, $key2), $key2));
        $this->assertSame(3.14, $helper->decrypt($helper->encrypt(3.14, $key2), $key2));
        $this->assertSame(true, $helper->decrypt($helper->encrypt(true, $key2), $key2));
        $this->assertSame(false, $helper->decrypt($helper->encrypt(false, $key2), $key2));

        $this->assertSame('foo', $helper->decrypt($helper->encrypt('foo', $key1, false), $key1, false));
        $this->assertSame('2', $helper->decrypt($helper->encrypt(2, $key1, false), $key1, false));
    }

    /**
     * @return void
     */
    public function test_encrypt_decrypt_binary()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertSame('foo', $helper->decryptBinary($helper->encryptBinary('foo')));
        $this->assertSame('bar', $helper->decryptBinary($helper->encryptBinary('bar')));
        $this->assertSame('2', $helper->decryptBinary($helper->encryptBinary(2)));
        $this->assertSame('3.14', $helper->decryptBinary($helper->encryptBinary(3.14)));
        $this->assertSame('', $helper->decryptBinary($helper->encryptBinary(null)));
    }
}
