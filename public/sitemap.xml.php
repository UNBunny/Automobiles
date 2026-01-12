<?php
/**
 * Динамическая генерация карты сайта в формате XML
 */

require_once 'bootstrap.php';

// Устанавливаем заголовок для XML
header('Content-Type: application/xml; charset=utf-8');

// Получаем базовый URL
$baseUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];

// Получаем данные из БД
$carModel = new CarModel();
$manufacturerModel = new ManufacturerModel();
$categoryModel = new CategoryModel();

$cars = $carModel->getAll([], 'newest', 1000); // Получаем до 1000 автомобилей
$manufacturers = $manufacturerModel->getAll();
$categories = $categoryModel->getAll();

// Функция для форматирования даты в ISO 8601
function formatDate($date = null) {
    if ($date) {
        return date('c', strtotime($date));
    }
    return date('c');
}

// Начинаем XML
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    <!-- Главная страница -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Страница всех автомобилей -->
    <url>
        <loc><?= $baseUrl ?>/cars.php</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Страница производителей -->
    <url>
        <loc><?= $baseUrl ?>/manufacturers.php</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Страница FAQ -->
    <url>
        <loc><?= $baseUrl ?>/faq.php</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Политика конфиденциальности -->
    <url>
        <loc><?= $baseUrl ?>/privacy.php</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>

    <!-- Условия использования -->
    <url>
        <loc><?= $baseUrl ?>/terms.php</loc>
        <lastmod><?= formatDate() ?></lastmod>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>

    <!-- Страницы категорий -->
    <?php foreach ($categories as $category): ?>
    <url>
        <loc><?= $baseUrl ?>/category.php?category=<?= urlencode($category['slug']) ?></loc>
        <lastmod><?= formatDate($category['updated_at'] ?? null) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>

    <!-- Страницы автомобилей -->
    <?php foreach ($cars as $car): ?>
    <url>
        <loc><?= $baseUrl ?>/car/<?= $car['id'] ?></loc>
        <lastmod><?= formatDate($car['updated_at'] ?? $car['created_at'] ?? null) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Страницы производителей (фильтр) -->
    <?php foreach ($manufacturers as $manufacturer): ?>
    <url>
        <loc><?= $baseUrl ?>/cars.php?manufacturer=<?= urlencode($manufacturer['slug']) ?></loc>
        <lastmod><?= formatDate($manufacturer['updated_at'] ?? null) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>

</urlset>
