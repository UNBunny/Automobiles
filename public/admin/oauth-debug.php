<?php
// Временная страница для отладки OAuth
session_start();

echo "<h1>OAuth Debug Info</h1>";
echo "<h3>GET параметры:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>SESSION:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_GET['code']) && isset($_GET['state'])) {
    echo "<h3>Попытка авторизации...</h3>";
    
    require_once '../bootstrap.php';
    
    $oauthConfig = require_once '../config/oauth.php';
    
    echo "<p>OAuth enabled: " . ($oauthConfig['yandex']['enabled'] ? 'YES ✅' : 'NO ❌') . "</p>";
    
    if ($oauthConfig['yandex']['enabled']) {
        try {
            $yandexOAuth = new YandexOAuth(
                $oauthConfig['yandex']['client_id'],
                $oauthConfig['yandex']['client_secret'],
                $oauthConfig['yandex']['redirect_uri']
            );
            
            echo "<p>✅ YandexOAuth объект создан</p>";
            
            // Проверяем state
            echo "<p>Проверка state...</p>";
            $stateValid = $yandexOAuth->verifyState($_GET['state']);
            echo "<p>State valid: " . ($stateValid ? 'YES ✅' : 'NO ❌') . "</p>";
            
            if (!$stateValid) {
                echo "<p style='color: red;'>❌ State verification failed!</p>";
                echo "<p>Ожидаемый state из сессии: " . ($_SESSION['oauth_state'] ?? 'НЕТ') . "</p>";
                echo "<p>Полученный state: " . $_GET['state'] . "</p>";
            } else {
                // Пробуем получить токен
                echo "<p>Обмен кода на токен...</p>";
                $tokenData = $yandexOAuth->getAccessToken($_GET['code']);
                
                if ($tokenData) {
                    echo "<p>✅ Токен получен</p>";
                    echo "<pre>";
                    print_r($tokenData);
                    echo "</pre>";
                    
                    // Получаем данные пользователя
                    echo "<p>Получение информации о пользователе...</p>";
                    $userInfo = $yandexOAuth->getUserInfo($tokenData['access_token']);
                    
                    if ($userInfo) {
                        echo "<p>✅ Данные пользователя получены:</p>";
                        echo "<pre>";
                        print_r($userInfo);
                        echo "</pre>";
                    } else {
                        echo "<p style='color: red;'>❌ Не удалось получить данные пользователя</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Не удалось получить токен</p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}

echo "<hr>";
echo "<a href='login.php'>← Вернуться на страницу входа</a>";
?>
