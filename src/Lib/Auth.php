<?php

namespace PXP\Lib;

use PXP\Models\User;

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
            session(['errors' => ['Login data incorrect']]);

            return false;
        }

        if (! password_verify($password, $user->password_hash)) {
            session(['errors' => ['Login data incorrect']]);

            return false;
        }

        session_regenerate_id();
        session(['username' => $username]);

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
