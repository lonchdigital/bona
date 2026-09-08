<?php

return [
    'enabled' => filter_var(env('TELEGRAM_NOTIFICATIONS_ENABLED', false), FILTER_VALIDATE_BOOL),
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),
    'message_thread_id' => env('TELEGRAM_MESSAGE_THREAD_ID'),
    'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
    'connect_timeout' => (float) env('TELEGRAM_CONNECT_TIMEOUT', 5),
    'timeout' => (float) env('TELEGRAM_TIMEOUT', 10),
];
