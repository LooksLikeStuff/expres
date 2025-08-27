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
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Яндекс Диск
    |--------------------------------------------------------------------------
    |
    | Настройки для работы с Яндекс.Диском с оптимизацией для больших файлов
    |
    */
    'yandex_disk' => [
        'token' => env('YANDEX_DISK_TOKEN', 'y0__xD-1-GlqveAAhjblgMgy8zl_BIVhC5iLWbQTnJiXBfnjmS39_7EUA'),
        'base_folder' => env('YANDEX_DISK_BASE_FOLDER', 'lk_deals'), // Базовая папка
        'timeout' => env('YANDEX_DISK_TIMEOUT', 0), // Убираем ограичения времени
        'chunk_size' => env('YANDEX_DISK_CHUNK_SIZE', 2097152), // 2MB для новой системы v3.0
        'max_retries' => env('YANDEX_DISK_MAX_RETRIES', 3), // Количество повторных попыток
        'min_speed' => env('YANDEX_DISK_MIN_SPEED', 1048576), // Минимальная скорость 1MB/s
        'low_speed_time' => env('YANDEX_DISK_LOW_SPEED_TIME', 1800), // 30 минут низкой скорости
        'retry_delay' => env('YANDEX_DISK_RETRY_DELAY', 2000), // Задержка между попытками (мс)
        'buffer_size' => env('YANDEX_DISK_BUFFER_SIZE', 33554432), // 32MB буфер
        'tcp_keepalive' => env('YANDEX_DISK_TCP_KEEPALIVE', true), // TCP Keep-Alive
        'tcp_nodelay' => env('YANDEX_DISK_TCP_NODELAY', true), // Отключить алгоритм Нейгла
        'connect_timeout' => env('YANDEX_DISK_CONNECT_TIMEOUT', 120), // 2 минуты на соединение
    ],
    'smsru' => [
        'api_key' => env('SMSRU_API_KEY', null),
        'api_id' => env('SMS_RU_API_ID', null),
    ],


];
