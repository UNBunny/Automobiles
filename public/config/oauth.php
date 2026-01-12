<?php

/**
 * Конфигурация OAuth провайдеров
 * 
 * ВАЖНО: Создайте файл .env в корне проекта со следующими переменными:
 * YANDEX_CLIENT_ID=your_client_id
 * YANDEX_CLIENT_SECRET=your_client_secret
 */

return [
    'yandex' => [
        'client_id' => $_ENV['OAUTH_CLIENT_ID'] ?? $_ENV['YANDEX_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['OAUTH_CLIENT_SECRET'] ?? $_ENV['YANDEX_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['OAUTH_REDIRECT_URI'] ?? 
                         'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/admin/index.php',
        'enabled' => !empty($_ENV['OAUTH_CLIENT_ID']) || !empty($_ENV['YANDEX_CLIENT_ID'])
    ]
];
