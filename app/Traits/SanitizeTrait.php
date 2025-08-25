<?php

namespace App\Traits;

trait SanitizeTrait
{
    public static function sanitize(?string $value): string
    {
        return trim(preg_replace('/^\s+|\s+$/u', '', strip_tags($value ?? '')));
    }
}
