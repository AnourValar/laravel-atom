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
     * @return string
     */
    public function encrypt($decryptedData, string $key): string // php artisan key:generate --show
    {
        $encrypter = new \Illuminate\Encryption\Encrypter(base64_decode($key), config('app.cipher'));
        return $encrypter->encrypt($decryptedData);
    }

    /**
     * Decrypt with a custom key
     *
     * @param string $encryptedData
     * @param string $key
     * @return mixed
     */
    public function decrypt(string $encryptedData, string $key)
    {
        $encrypter = new \Illuminate\Encryption\Encrypter(base64_decode($key), config('app.cipher'));
        return $encrypter->decrypt($encryptedData);
    }
}
