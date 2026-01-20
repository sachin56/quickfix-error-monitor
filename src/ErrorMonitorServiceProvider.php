<?php

namespace QuickFix\ErrorMonitor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;

class ErrorMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/quickfix.php',
            'quickfix'
        );
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->registerExceptionListener();
        $this->registerLogListener();
    }

    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/quickfix.php' => config_path('quickfix.php'),
            ], 'quickfix-config');
        }
    }

    protected function registerExceptionListener(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);
        $originalReport = [$handler, 'report'];

        $handler->report = function ($exception) use ($originalReport) {
            try {
                ErrorSender::send($exception);
            } catch (\Throwable $e) {
                // Prevent loop
            }

            return $originalReport($exception);
        };
    }

    protected function registerLogListener(): void
    {
        Log::listen(function ($level, $message, $context) {
            if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
                ErrorSender::sendMessage($message, $context);
            }
        });
    }
}
