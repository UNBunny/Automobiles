<?php
require_once 'config.php';

$oauthConfig = require_once '../config/oauth.php';

// Проверяем, включен ли Yandex OAuth
if (!$oauthConfig['yandex']['enabled']) {
    die('Yandex OAuth не настроен. Пожалуйста, добавьте YANDEX_CLIENT_ID и YANDEX_CLIENT_SECRET в .env файл.');
}

$error = '';

// Обработка callback от Yandex
if (isset($_GET['code']) && isset($_GET['state'])) {
    try {
        $yandexOAuth = new YandexOAuth(
            $oauthConfig['yandex']['client_id'],
            $oauthConfig['yandex']['client_secret'],
            $oauthConfig['yandex']['redirect_uri']
        );
        
        if ($yandexOAuth->authenticateUser($_GET['code'], $_GET['state'])) {
            // Успешная авторизация
            Utils::redirect('/admin/index.php');
        } else {
            $error = 'Ошибка авторизации через Yandex';
        }
        
    } catch (Exception $e) {
        error_log("OAuth callback error: " . $e->getMessage());
        $error = 'Произошла ошибка при авторизации';
    }
} elseif (isset($_GET['error'])) {
    // Пользователь отклонил авторизацию
    $error = 'Авторизация отменена';
}

// Если есть ошибка, перенаправляем на страницу входа с сообщением
if ($error) {
    $_SESSION['oauth_error'] = $error;
    Utils::redirect('/admin/login.php');
}
?>
