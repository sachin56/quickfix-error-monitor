<?php

return [
    'enabled' => env('QUICKFIX_ENABLED', true),
    
    // The device token for your specific phone
    'fcm_token' => env('QUICKFIX_FCM_TOKEN'),

    // Environments where the monitor is active
    'environments' => ['production', 'staging', 'local'],
];