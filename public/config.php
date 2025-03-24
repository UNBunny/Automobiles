<?php
// Настройки базы данных
define('DB_HOST', 'postgres');
define('DB_NAME', 'car_db');
define('DB_USER', 'nikita');
define('DB_PASS', 'root');

// Базовый URL приложения
define('BASE_URL', 'http://localhost');

// Настройки для работы с путями
define('ASSETS_PATH', '/assets');
define('IMAGES_PATH', ASSETS_PATH . '/images');

// Подключение к базе данных
try {
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Функция для безопасного вывода данных
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>