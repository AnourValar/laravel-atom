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
     * @param string $phone
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
     * @param string $email
     * @return string
     */
    public function canonizeEmail(?string $email): string
    {
        return trim( mb_strtolower($email) );
    }

    /**
     * Generates code
     *
     * @param int $length
     * @return string
     */
    public function generateCode(int $length): string
    {
        $code = '';

        while (mb_strlen($code) < $length) {
            if (config('app.debug')) {
                $code .= '0';
            } else {
                $code .= random_int(0, 9);
            }
        }

        return $code;
    }
}
