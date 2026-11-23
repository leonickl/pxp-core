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
        if (! file_exists(path('log'))) {
            mkdir(path('log'));
        }

        if (! file_exists(self::file())) {
            file_put_contents(self::file(), 'Logs at '.date('Y-m-d')."\n".str_repeat('=', 18)."\n");
        }

        file_put_contents(self::file(), PHP_EOL.date('Y-m-d H:i:s').': '.$data, FILE_APPEND);
    }
}
