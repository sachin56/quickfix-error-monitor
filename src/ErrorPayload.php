<?php

namespace QuickFix\ErrorMonitor;

use Throwable;

class ErrorPayload
{
    public static function fromException(Throwable $e): array
    {
        return [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 10),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => auth()->id(),
            'env' => app()->environment(),
            'time' => now()->toDateTimeString(),
        ];
    }

    public static function fromMessage(string $message, array $context = []): array
    {
        return [
            'type' => 'log',
            'message' => $message,
            'context' => self::sanitize($context),
            'url' => request()->fullUrl(),
            'env' => app()->environment(),
            'time' => now()->toDateTimeString(),
        ];
    }

    protected static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (str_contains(strtolower($key), 'password')) {
                $data[$key] = '******';
            }
        }
        return $data;
    }
}