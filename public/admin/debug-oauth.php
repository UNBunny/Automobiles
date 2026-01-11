<?php
require_once '../bootstrap.php';

echo "<h2>Отладка OAuth конфигурации</h2>";

echo "<h3>1. Переменные окружения:</h3>";
echo "<pre>";
echo "YANDEX_CLIENT_ID: " . (isset($_ENV['YANDEX_CLIENT_ID']) ? $_ENV['YANDEX_CLIENT_ID'] : 'НЕ УСТАНОВЛЕНО') . "\n";
echo "YANDEX_CLIENT_SECRET: " . (isset($_ENV['YANDEX_CLIENT_SECRET']) ? '***' . substr($_ENV['YANDEX_CLIENT_SECRET'], -4) : 'НЕ УСТАНОВЛЕНО') . "\n";
echo "</pre>";

echo "<h3>2. OAuth конфигурация:</h3>";
$oauthConfig = require_once '../config/oauth.php';
echo "<pre>";
print_r([
    'yandex' => [
        'client_id' => $oauthConfig['yandex']['client_id'] ?: 'ПУСТО',
        'client_secret' => $oauthConfig['yandex']['client_secret'] ? '***' . substr($oauthConfig['yandex']['client_secret'], -4) : 'ПУСТО',
        'redirect_uri' => $oauthConfig['yandex']['redirect_uri'],
        'enabled' => $oauthConfig['yandex']['enabled'] ? 'TRUE ✅' : 'FALSE ❌'
    ]
]);
echo "</pre>";

echo "<h3>3. Проверка класса YandexOAuth:</h3>";
if (class_exists('YandexOAuth')) {
    echo "✅ Класс YandexOAuth загружен<br>";
    
    if ($oauthConfig['yandex']['enabled']) {
        try {
            $yandexOAuth = new YandexOAuth(
                $oauthConfig['yandex']['client_id'],
                $oauthConfig['yandex']['client_secret'],
                $oauthConfig['yandex']['redirect_uri']
            );
            $authUrl = $yandexOAuth->getAuthUrl();
            echo "✅ YandexOAuth инициализирован<br>";
            echo "Auth URL: <a href='$authUrl' target='_blank'>$authUrl</a><br>";
        } catch (Exception $e) {
            echo "❌ Ошибка инициализации: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "⚠️ Yandex OAuth отключен (enabled = false)<br>";
    }
} else {
    echo "❌ Класс YandexOAuth не найден<br>";
}

echo "<h3>4. Файл .env:</h3>";
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    echo "✅ Файл .env существует: $envPath<br>";
} else {
    echo "❌ Файл .env не найден: $envPath<br>";
}

echo "<hr>";
echo "<a href='login.php'>← Вернуться на страницу входа</a>";
?>
