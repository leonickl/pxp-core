<?php

namespace PXP\Lib;

class Log
{
    private static function file(): string
    {
        return path('log/'.date('Y-m-d').'.log');
    }

    public static function log(string $data): void
    {
        file_put_contents(self::file(), PHP_EOL.date('Y-m-d H:i:s').': '.$data, FILE_APPEND);
    }
}
