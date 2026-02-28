<?php

namespace PXP\Core\Lib;

use PXP\Ds\Obj;

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
            'samesite' => 'None', // TODO: unsecure because of auth
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

    public static function setAll(array $data)
    {
        foreach ($data as $key => $value) {
            Session::set($key, $value);
        }
    }

    public static function unset(string $key)
    {
        unset($_SESSION[$key]);
    }

    public static function get(string $key, mixed $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function getAll(array $keys): Obj
    {
        return o(...$_SESSION)->only(...$keys);
    }

    public static function take(string $key, mixed $default = null)
    {
        $value = self::get($key, $default);
        self::unset($key);
        return $value;
    }

    public static function access(string|array|null $key = null): mixed
    {
        if (is_array($key)) {
            return self::getAll($key);
        }

        if ($key === null) {
            return o(...$_SESSION);
        }

        return $_SESSION[$key] ?? null;
    }
}
