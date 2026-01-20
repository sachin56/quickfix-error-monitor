<?php

namespace QuickFix\ErrorMonitor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorSender
{
    public static function send(Throwable $exception): void
    {
        if (! self::shouldSend()) {
            return;
        }

        try {
            Http::timeout(3)
                ->withHeaders(self::headers())
                ->post(config('quickfix.endpoint'),
                    ErrorPayload::fromException($exception)
                );
        } catch (Throwable $e) {
            Log::debug('[QuickFix] Failed sending exception');
        }
    }

    public static function sendMessage(string $message, array $context = []): void
    {
        if (! self::shouldSend()) {
            return;
        }

        try {
            Http::timeout(3)
                ->withHeaders(self::headers())
                ->post(config('quickfix.endpoint'),
                    ErrorPayload::fromMessage($message, $context)
                );
        } catch (Throwable $e) {
            Log::debug('[QuickFix] Failed sending log message');
        }
    }

    protected static function shouldSend(): bool
    {
        return config('quickfix.enabled')
            && config('quickfix.endpoint')
            && in_array(app()->environment(), config('quickfix.environments', []));
    }

    protected static function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Project-Key' => config('quickfix.project_key'),
            'X-App-Env' => app()->environment(),
        ];
    }
}
