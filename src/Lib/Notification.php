<?php

namespace PXP\Lib;

readonly class Notification
{
    private function __construct(public string $type, public string $content) {}

    private static function make(string $type, string $content): void
    {
        $notifications = session()->array('notifications');

        session([
            'notifications' => [...$notifications, new self($type, $content)],
        ]);
    }

    public static function info(string $content): void
    {
        self::make('info', $content);
    }

    public static function success(string $content): void
    {
        self::make('success', $content);
    }

    public static function warn(string $content): void
    {
        self::make('warn', $content);
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return session()->take('notifications', []);
    }
}
