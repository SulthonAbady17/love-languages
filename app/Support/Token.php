<?php

namespace App\Support;

class Token
{
    private const LENGTH = 16;

    private const CHARACTERS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * A short, unguessable token: 16 characters drawn uniformly from a-z0-9.
     */
    public static function generate(): string
    {
        $token = '';
        $last = strlen(self::CHARACTERS) - 1;

        for ($position = 0; $position < self::LENGTH; $position++) {
            $token .= self::CHARACTERS[random_int(0, $last)];
        }

        return $token;
    }
}
