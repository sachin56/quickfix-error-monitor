<?php

namespace QuickFix\ErrorMonitor;

use Throwable;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

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
            $messaging = app('firebase.messaging');
            $fcmToken = config('quickfix.fcm_token');

            if (!$fcmToken) {
                Log::channel('single')->debug('[QuickFix] Missing FCM_TOKEN in .env');
                return;
            }

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create(
                    '🚨 Error: ' . app()->environment(),
                    substr($exception->getMessage(), 0, 150)
                ))
                ->withData([
                    'file' => (string) $exception->getFile(),
                    'line' => (string) $exception->getLine(),
                    'type' => (string) get_class($exception),
                ]);

            $messaging->send($message);

        } catch (Throwable $e) {
            // Log locally only to avoid recursion
            Log::channel('single')->debug('[QuickFix] Firebase Failed: ' . $e->getMessage());
        } finally {
            self::$isReporting = false;
        }
    }

    protected static function shouldSend(): bool
    {
        return config('quickfix.enabled') 
            && in_array(app()->environment(), config('quickfix.environments', []));
    }
}