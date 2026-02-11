<?php

namespace QuickFix\ErrorMonitor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class ErrorMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quickfix.php', 'quickfix');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/quickfix.php' => config_path('quickfix.php'),
            ], 'quickfix-config');
        }

        $this->registerLogListener();
    }

    protected function registerLogListener(): void
    {
        Log::listen(function ($level, $message, $context) {
            $monitoredLevels = ['error', 'critical', 'alert', 'emergency'];
            
            if (in_array($level, $monitoredLevels)) {
                // If the message is an actual Exception object in context, send that
                if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
                    ErrorSender::send($context['exception']);
                } else {
                    ErrorSender::sendMessage($message, $context);
                }
            }
        });
    }
}