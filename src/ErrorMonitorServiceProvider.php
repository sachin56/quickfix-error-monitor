<?php

namespace QuickFix\ErrorMonitor;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;

class ErrorMonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quickfix.php', 'quickfix');

        // Auto-register Firebase Service Provider if not loaded
        if (!$this->app->providerIsLoaded(\Kreait\Laravel\Firebase\ServiceProvider::class)) {
            $this->app->register(\Kreait\Laravel\Firebase\ServiceProvider::class);
        }
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
        Log::listen(function (MessageLogged $event) {
            $criticalLevels = ['error', 'critical', 'alert', 'emergency'];

            // Access properties from the $event object
            if (in_array($event->level, $criticalLevels)) {
                if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
                    ErrorSender::send($event->context['exception']);
                }
            }
        });
}
}