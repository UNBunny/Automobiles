<?php
/**
 * Скрипт миграции базы данных для Render.com
 * Выполните один раз после деплоя: https://your-app.onrender.com/migrate.php
 */

require_once 'bootstrap.php';

// Проверка, что таблицы еще не созданы
try {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT to_regclass('public.categories')");
    
    if ($result['to_regclass'] !== null) {
        die('✅ База данных уже инициализирована!');
    }
} catch (Exception $e) {
    // Продолжаем, если ошибка
}

// Читаем SQL из init.sql
$sqlFile = __DIR__ . '/../postgres/init.sql';
if (!file_exists($sqlFile)) {
    die('❌ Файл init.sql не найден!');
}

$sql = file_get_contents($sqlFile);

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Выполняем SQL
    $pdo->exec($sql);
    
    echo "✅ База данных успешно инициализирована!<br>";
    echo "✅ Таблицы созданы<br>";
    echo "✅ Тестовые данные загружены<br>";
    echo "<br><a href='/'>Перейти на главную</a>";
    
} catch (Exception $e) {
    echo "❌ Ошибка миграции: " . $e->getMessage();
}
