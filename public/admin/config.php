<?php
// Подключаем главный конфиг
require_once '../bootstrap.php';

// Функции для обратной совместимости админки
function checkAuth() {
    Auth::requireAuth();
}

function generateCSRFToken() {
    return Auth::generateCSRFToken();
}

function verifyCSRFToken($token) {
    return Auth::verifyCSRFToken($token);
}

// Константы для админки
define('ADMIN_BASE_URL', 'http://localhost/admin');
define('ADMIN_ASSETS_PATH', '/assets');
define('ADMIN_IMAGES_PATH', ADMIN_ASSETS_PATH . '/images');