<?php
require_once 'bootstrap.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Utils::redirect('/');
}

$carId = (int)$_GET['id'];

// Используем модель
$carModel = new CarModel();
$car = $carModel->getById($carId);

if (!$car) {
    Utils::redirect('/');
}

// Увеличиваем счетчик просмотров
$carModel->incrementViews($carId);

// Получаем дополнительные данные через базу данных
$db = Database::getInstance();

// Get car images
$images = $db->fetchAll("SELECT * FROM car_images WHERE car_id = ?", [$carId]);

// Get car features
$features = $db->fetchAll("SELECT * FROM car_features WHERE car_id = ?", [$carId]);

// Get car categories
$categories = $db->fetchAll("
    SELECT cat.name, cat.slug 
    FROM car_categories cc
    JOIN categories cat ON cc.category_id = cat.id
    WHERE cc.car_id = ?
", [$carId]);

$pageTitle = ($car['manufacturer_name'] ?? '') . " " . ($car['model'] ?? '');
require_once 'templates/header.php';
?>

<div class="container">
    <!-- Основная секция с фото и характеристиками -->
    <div class="vehicle-section">
        <div class="vehicle-image">
            <img src="<?= Utils::escape($car['main_image_url'] ?? "") ?>" alt="<?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '')) ?>">
        </div>
        <div class="vehicle-specs">
            <h1><?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '') . ' ' . ($car['year'] ?? '')) ?></h1>
            <table>
                <tr>
                    <td>Год выпуска:</td>
                    <td><?= Utils::escape($car['year'] ?? "") ?></td>
                </tr>
                <tr>
                    <td>Тип кузова:</td>
                    <td><?= Utils::escape($car['body_type_name'] ?? "") ?></td>
                </tr>
                <tr>
                    <td>Тип двигателя:</td>
                    <td><?= Utils::escape($car['engine_type_name'] ?? "") ?></td>
                </tr>
                <tr>
                    <td>Мощность:</td>
                    <td><?= Utils::escape($car['power_hp'] ?? "") ?> л.с.</td>
                </tr>
                <tr>
                    <td>Разгон 0-100 км/ч:</td>
                    <td><?= Utils::escape($car['acceleration_0_100'] ?? "") ?> сек</td>
                </tr>
                <tr>
                    <td>Макс. скорость:</td>
                    <td><?= Utils::escape($car['top_speed_kmh'] ?? "") ?> км/ч</td>
                </tr>
                <?php if (($car['engine_type_name'] ?? '') === 'Электрический'): ?>
                <tr>
                    <td>Ёмкость батареи:</td>
                    <td><?= Utils::escape($car['battery_capacity_kwh'] ?? "") ?> кВт·ч</td>
                </tr>
                <tr>
                    <td>Запас хода:</td>
                    <td><?= Utils::escape($car['range_km'] ?? "") ?> км</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Цена:</td>
                    <td>$<?= number_format($car['price'] ?? 0, 2) ?></td>
                </tr>
                <?php foreach ($features as $feature): ?>
                <tr>
                    <td><?= Utils::escape($feature['feature_name'] ?? "") ?>:</td>
                    <td><?= Utils::escape($feature['feature_value'] ?? "") ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <?php if (count($images) > 1): ?>
    <div class="additional-images">
        <h2>Дополнительные фото</h2>
        <div class="image-grid">
            <?php foreach ($images as $image): ?>
                <?php if (!$image['is_main']): ?>
                <img src="<?= Utils::escape($image['image_url'] ?? "") ?>" alt="<?= Utils::escape($image['alt_text'] ?: (($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? ''))) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Описание -->
    <div class="vehicle-description">
        <p><?= Utils::escape($car['description'] ?? "") ?></p>
        
        <?php if (!empty($categories)): ?>
        <h2>Категории:</h2>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <span class="category-tag"><?= Utils::escape($category['name'] ?? "") ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
