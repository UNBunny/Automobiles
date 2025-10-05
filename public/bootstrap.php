<?php

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