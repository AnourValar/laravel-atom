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
        $phone = preg_replace('|^8([^5]\d{9})$|', '7$1', $phone);

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
     * Cleans tags
     *
     * @param array $tags
     * @param string $content
     * @return string|null
     */
    public function stripTags(array $tags, ?string $content): ?string
    {
        if (is_null($content)) {
            return $content;
        }

        foreach ($tags as $tag) {
            $content = preg_replace('#<\/?'.preg_quote($tag).'(>|\s[^>]*>)#iu', '', $content);
        }

        return $content;
    }
}
