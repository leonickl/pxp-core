<?php

namespace PXP\Core\Lib;

class Session
{
    public static function start()
    {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => config('domain'),
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);

        session_start();
    }

    public static function destroy()
    {
        session_destroy();
    }

    public static function set(string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function unset(string $key)
    {
        unset($_SESSION[$key]);
    }

    public static function setAll(array $data)
    {
        foreach ($data as $key => $value) {
            Session::set($key, $value);
        }
    }

    public static function get(string $key, mixed $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function take(string $key, mixed $default = null)
    {
        $value = self::get($key, $default);
        self::unset($key);

        return $value;
    }
}
