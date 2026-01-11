<?php

/**
 * Загрузка переменных окружения из .env файла
 */
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Пропускаем комментарии
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Парсим переменные окружения
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Удаляем кавычки если есть
            $value = trim($value, '"\'');
            
            // Устанавливаем переменную окружения
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

/**
 * Автозагрузчик классов
 */
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/classes/' . $className . '.php';
    
    if (file_exists($classFile)) {
        require_once $classFile;
        return true;
    }
    
    // Проверяем, есть ли класс в ReferenceModels.php
    $referenceFile = __DIR__ . '/classes/ReferenceModels.php';
    if (file_exists($referenceFile) && 
        in_array($className, ['BaseReferenceModel', 'CategoryModel', 'BodyTypeModel', 'EngineTypeModel'])) {
        require_once $referenceFile;
        return true;
    }
    
    return false;
});

// Настройки безопасности для сессий
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax'); // Lax позволяет OAuth редиректы!
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_secure', '0'); // Установите '1' если используете HTTPS

// Старт сессии если еще не стартована
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Константы приложения
define('APP_ROOT', __DIR__);
define('BASE_URL', 'http://localhost');
define('ASSETS_PATH', '/assets');
define('IMAGES_PATH', ASSETS_PATH . '/images');

// Функции для обратной совместимости
function escape($data) {
    return Utils::escape($data);
}

function createSlug($string) {
    return Utils::createSlug($string);
}

function formatDate($date) {
    return Utils::formatDate($date);
}

function uploadImage($file, $targetDir = '../assets/images/') {
    return Utils::uploadImage($file, $targetDir);
}

// Глобальная переменная для обратной совместимости
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}