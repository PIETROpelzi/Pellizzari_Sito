<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mqtt' => [
        'host' => env('MQTT_HOST'),
        'port' => (int) env('MQTT_PORT', 1883),
        'username' => env('MQTT_USERNAME'),
        'password' => env('MQTT_PASSWORD'),
        'client_id' => env('MQTT_CLIENT_ID', 'smart-dispenser-web'),
        'use_tls' => (bool) env('MQTT_USE_TLS', false),
        'clean_session' => (bool) env('MQTT_CLEAN_SESSION', true),
        'topic_root' => env('MQTT_TOPIC_ROOT', 'smart-dispenser'),
        'topic_telemetry_suffix' => env('MQTT_TOPIC_TELEMETRY_SUFFIX', 'events/telemetry'),
        'topic_dose_log_suffix' => env('MQTT_TOPIC_DOSE_LOG_SUFFIX', 'events/dose-log'),
        'topic_status_suffix' => env('MQTT_TOPIC_STATUS_SUFFIX', 'status'),
    ],

];
