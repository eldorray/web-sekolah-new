<?php

declare(strict_types=1);

namespace App\Support;

use Inertia\Inertia;

/**
 * Queues a toast for the next Inertia response.
 *
 * Inertia v3 carries flash data itself and fires the client `flash` event
 * from it, so a plain session flash never reaches the toaster.
 */
final class Toast
{
    public static function success(string $message): void
    {
        self::push('success', $message);
    }

    public static function error(string $message): void
    {
        self::push('error', $message);
    }

    private static function push(string $type, string $message): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }
}
