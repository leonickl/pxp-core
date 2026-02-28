<?php

namespace App;

use App\Models\User;
use PXP\Core\Lib\Session;

/**
 * Facade for interactive authentication.
 * Cannot be used for basic auth.
 */
class Auth
{
    public static function login(string $username, string $password): bool
    {
        $user = User::findByOrNull('username', $username);

        if ($user === null) {
            Session::set('errors', ['Login data incorrect']);

            return false;
        }

        if (! password_verify($password, $user->password_hash)) {
            Session::set('errors', ['Login data incorrect']);

            return false;
        }

        session_regenerate_id();
        Session::set('username', $username);

        return true;
    }

    public static function logout(): void
    {
        session_destroy();
    }

    public static function auth(): bool
    {
        return session('username') !== null;
    }

    public static function user(): ?User
    {
        return self::auth()
            ? User::findByOrNull('username', session('username'))
            : null;
    }
}
