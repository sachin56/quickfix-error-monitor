<?php

return [
    'enabled' => env('QUICKFIX_ENABLED', true),
    'endpoint' => env('QUICKFIX_ENDPOINT'),
    'project_key' => env('QUICKFIX_PROJECT_KEY'),
    'environments' => ['production', 'staging', 'local'], // Added 'local' for your testing
];