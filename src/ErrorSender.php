<?php

namespace QuickFix\ErrorMonitor;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorSender
{
    protected static bool $isReporting = false;

    public static function send(Throwable $exception): void
    {
        if (self::$isReporting || !self::shouldSend()) {
            return;
        }

        self::$isReporting = true;

        try {
            Http::timeout(3)
                ->withHeaders(self::headers())
                ->post(config('quickfix.endpoint'), ErrorPayload::fromException($exception));
        } catch (Throwable $e) {
            // Silently log to local file only so we don't trigger the listener again
            Log::channel('single')->debug('[QuickFix] Failed sending exception: ' . $e->getMessage());
        } finally {
            self::$isReporting = false;
        }
    }

    public static function sendMessage(string $message, array $context = []): void
    {
        if (self::$isReporting || !self::shouldSend()) {
            return;
        }

        self::$isReporting = true;

        try {
            Http::timeout(3)
                ->withHeaders(self::headers())
                ->post(config('quickfix.endpoint'), ErrorPayload::fromMessage($message, $context));
        } catch (Throwable $e) {
            Log::channel('single')->debug('[QuickFix] Failed sending log message');
        } finally {
            self::$isReporting = false;
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