<?php

use QuickFix\ErrorMonitor\ErrorSender;

if (! function_exists('quickfix_exception')) {
    function quickfix_exception(Throwable $e): void
    {
        ErrorSender::send($e);
    }
}

if (! function_exists('quickfix_message')) {
    function quickfix_message(string $message, array $context = []): void
    {
        ErrorSender::sendMessage($message, $context);
    }
}
