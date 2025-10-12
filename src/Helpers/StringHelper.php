<?php

namespace AnourValar\LaravelAtom\Helpers;

class StringHelper
{
    /**
     * Canonize: phone number
     *
     * @param string $phone
     * @param bool $withPlus
     * @return string
     */
    public function canonizePhone(?string $phone, bool $withPlus = false): string
    {
        $phone = preg_replace('|[^\d]|u', '', (string) $phone);
        $phone = preg_replace('|^8([^5]\d{9})$|', '7$1', $phone); // internal alias

        if ($withPlus) {
            $phone = "+$phone";
        }

        return $phone;
    }

    /**
     * Formatting (for display): phone number
     *
     * @param string|null $phone
     * @return string|null
     */
    public function formatPhone(?string $phone): ?string
    {
        if (! $phone) {
            return $phone;
        }

        if (mb_strlen($phone) == 11) {
            return preg_replace('|^(\d+)(\d{3})(\d{3})(\d{2})(\d{2})$|', '+$1($2) $3-$4$5', $phone);
        }

        return '+' . preg_replace('|(.{3}(?=.{2,}))|', '$1-', $phone);
    }

    /**
     * Canonize: e-mail
     *
     * @param string|null $email
     * @return string|null
     */
    public function canonizeEmail(?string $email): ?string
    {
        if (! $email) {
            return $email;
        }

        return trim(mb_strtolower($email));
    }

    /**
     * Cleans tags and its innerhtml
     *
     * @param string|null $content
     * @return string|null
     */
    /*public function stripTags(?string $content): ?string
    {
        if (is_null($content)) {
            return $content;
        }

        return preg_replace('#<\/?[a-z\d]+(>|\s[^>]*>)#iu', '', $content);
    }*/

    /**
     * Encrypt with a custom key
     *
     * @param mixed $decryptedData
     * @param string $key
     * @param bool $serialize
     * @return string
     */
    public function encrypt($decryptedData, string $key, bool $serialize = true): string // php artisan key:generate --show -> without "base64:"
    {
        $encrypter = new \Illuminate\Encryption\Encrypter(base64_decode($key), config('app.cipher'));
        return $encrypter->encrypt($decryptedData, $serialize);
    }

    /**
     * Decrypt with a custom key
     *
     * @param string $encryptedData
     * @param string $key
     * @param bool $unserialize
     * @return mixed
     */
    public function decrypt(string $encryptedData, string $key, bool $unserialize = true)
    {
        $encrypter = new \Illuminate\Encryption\Encrypter(base64_decode($key), config('app.cipher'));
        return $encrypter->decrypt($encryptedData, $unserialize);
    }

    /**
     * Encrypt binary
     *
     * @param mixed $decryptedValue
     * @param string|null $key
     * @return string
     */
    public function encryptBinary($decryptedValue, ?string $key = null): string
    {
        if (! $key) {
            $key = config('app.key');
            if (\Str::startsWith($key, 'base64:')) {
                $key = substr($key, 7);
            }
        }

        $iv = random_bytes(openssl_cipher_iv_length(strtolower(config('app.cipher'))));
        $decryptedValue = \openssl_encrypt(
            $decryptedValue,
            strtolower(config('app.cipher')),
            base64_decode($key),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decryptedValue === false || $tag) {
            throw new \RuntimeException('Could not encrypt the data.');
        }

        return $iv.$decryptedValue;
    }

    /**
     * Decrypt binary
     *
     * @param string $encryptedData
     * @param string|null $key
     * @return string
     */
    public function decryptBinary(string $encryptedData, ?string $key = null): string
    {
        if (! $key) {
            $key = config('app.key');
            if (\Str::startsWith($key, 'base64:')) {
                $key = substr($key, 7);
            }
        }

        $ivLength = openssl_cipher_iv_length(strtolower(config('app.cipher')));
        $iv = substr($encryptedData, 0, $ivLength);
        $encryptedData = substr($encryptedData, $ivLength);

        $encryptedData = \openssl_decrypt(
            $encryptedData,
            strtolower(config('app.cipher')),
            base64_decode($key),
            OPENSSL_RAW_DATA,
            $iv,
            ''
        );

        if ($encryptedData === false) {
            throw new \RuntimeException('Could not decrypt the data.');
        }

        return $encryptedData;
    }
}
