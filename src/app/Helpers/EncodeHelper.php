<?php

declare(strict_types=1);

namespace App\Helpers;

class EncodeHelper
{
    public static function encode(string $content)
    {
        return base64_encode(gzencode($content));
    }

    public static function decode(string $content)
    {
        return gzdecode(base64_decode($content));
    }
}
