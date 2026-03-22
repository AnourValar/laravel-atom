<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class StringHelperTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @return void
     */
    public function test_canonizePhone()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertNull($helper->canonizePhone(null));
        $this->assertEquals('79051230000', $helper->canonizePhone('7 905 123 00-00'));
        $this->assertEquals('+79051230000', $helper->canonizePhone('7 905 123 00-00', true));
        $this->assertEquals('79051230000', $helper->canonizePhone(' 8 905 123 00-00 ', false));
    }

    /**
     * @return void
     */
    public function test_formatPhone()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertNull($helper->formatPhone(null));
        $this->assertEquals('+7(905) 123-4567', $helper->formatPhone('79051234567'));
        $this->assertEquals('+123-456', $helper->formatPhone('123456'));
    }

    /**
     * @return void
     */
    public function test_canonizeEmail()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertNull($helper->canonizeEmail(null));
        $this->assertEquals('foo@example.org', $helper->canonizeEmail('FOO@example.org'));
        $this->assertEquals('foo@example.org', $helper->canonizeEmail('foo@example.org'));
    }

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

    /**
     * @return void
     */
    public function test_mask()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertNull($helper->mask(null));
        $this->assertSame('', $helper->mask(''));
        $this->assertSame('*', $helper->mask('f'));
        $this->assertSame('*o', $helper->mask('fo'));
        $this->assertSame('f*o', $helper->mask('foo'));
        $this->assertSame('f**o', $helper->mask('fooo'));
        $this->assertSame('f***o', $helper->mask('foooo'));
        $this->assertSame('fo**ar', $helper->mask('foobar'));
        $this->assertSame('fo***r1', $helper->mask('foobar1'));
        $this->assertSame('fo****12', $helper->mask('foobar12'));
        $this->assertSame('fo*****23', $helper->mask('foobar123'));

        $this->assertSame('fo*****23', $helper->mask('foobar123', '@'));

        $this->assertNull($helper->mask(null, '@'));
        $this->assertSame('', $helper->mask('', '@'));
        $this->assertSame('*@example.org', $helper->mask('f@example.org', '@'));
        $this->assertSame('*o@example.org', $helper->mask('fo@example.org', '@'));
        $this->assertSame('f*o@example.org', $helper->mask('foo@example.org', '@'));
        $this->assertSame('f**o@example.org', $helper->mask('fooo@example.org', '@'));
        $this->assertSame('f***o@example.org', $helper->mask('foooo@example.org', '@'));
        $this->assertSame('fo**ar@example.org', $helper->mask('foobar@example.org', '@'));
        $this->assertSame('fo***r1@example.org', $helper->mask('foobar1@example.org', '@'));
        $this->assertSame('fo****12@example.org', $helper->mask('foobar12@example.org', '@'));
        $this->assertSame('fo*****23@example.org', $helper->mask('foobar123@example.org', '@'));
    }

    /**
     * @return void
     */
    public function test_name_full()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertSame(null, $helper->nameFull(null, null, null));
        $this->assertSame(null, $helper->nameFull(null, null, 'Петрович'));
        $this->assertSame('Василий', $helper->nameFull(null, 'Василий', null));
        $this->assertSame('Василий Петрович', $helper->nameFull(null, 'Василий', 'Петрович'));
        $this->assertSame('Иванов', $helper->nameFull('Иванов', null, null));
        $this->assertSame('Иванов', $helper->nameFull('Иванов', null, 'Петрович'));
        $this->assertSame('Иванов Василий', $helper->nameFull('Иванов', 'Василий', null));
        $this->assertSame('Иванов Василий Петрович', $helper->nameFull('Иванов', 'Василий', 'Петрович'));
    }

    /**
     * @return void
     */
    public function test_name_short()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\StringHelper();

        $this->assertSame(null, $helper->nameShort(null, null, null));
        $this->assertSame(null, $helper->nameShort(null, null, 'Петрович'));
        $this->assertSame('Василий', $helper->nameShort(null, 'Василий', null));
        $this->assertSame('Василий', $helper->nameShort(null, 'Василий', 'Петрович'));
        $this->assertSame('Иванов', $helper->nameShort('Иванов', null, null));
        $this->assertSame('Иванов', $helper->nameShort('Иванов', null, 'Петрович'));
        $this->assertSame('Иванов В.', $helper->nameShort('Иванов', 'Василий', null));
        $this->assertSame('Иванов В.П.', $helper->nameShort('Иванов', 'Василий', 'Петрович'));
    }
}
