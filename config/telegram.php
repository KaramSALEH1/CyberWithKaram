<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Bot token issued by @BotFather. Loaded from TELEGRAM_BOT_TOKEN only —
    | there is deliberately NO hardcoded fallback, so a missing value disables
    | notifications instead of leaking to an unexpected chat.
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Target Chat ID
    |--------------------------------------------------------------------------
    |
    | The single destination for every CyberLogia notification.
    | Loaded from TELEGRAM_CHAT_ID only — no defaults, no legacy fallbacks.
    |
    | Must be the NUMERIC chat id of the operations chat (@kachat3), e.g.
    |   - group / supergroup  -> -1001234567890
    |   - private chat        ->  123456789
    | A Telegram @username cannot be sent to directly; resolve the numeric id
    | first (add the bot to the chat, send any message, then run
    | `php artisan telegram:test`, which reports the id it resolved).
    |
    */

    'chat_id' => env('TELEGRAM_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('TELEGRAM_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Human-readable label for the target chat (used in the test command)
    |--------------------------------------------------------------------------
    */

    'chat_label' => env('TELEGRAM_CHAT_LABEL', '@kachat3'),

];
