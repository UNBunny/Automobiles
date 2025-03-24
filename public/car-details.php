<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: /");
    exit;
}

$carId = $_GET['id'];

// Get car details
$stmt = $pdo->prepare("
    SELECT c.*, m.name as manufacturer_name, m.logo_url as manufacturer_logo, 
           b.name as body_type, e.name as engine_type
    FROM cars c
    JOIN manufacturers m ON c.manufacturer_id = m.id
    JOIN body_types b ON c.body_type_id = b.id
    JOIN engine_types e ON c.engine_type_id = e.id
    WHERE c.id = ?
");
$stmt->execute([$carId]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: /");
    exit;
}

// Update view count
$pdo->prepare("UPDATE cars SET views = views + 1 WHERE id = ?")->execute([$carId]);

// Get car images
$imagesStmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ?");
$imagesStmt->execute([$carId]);
$images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get car features
$featuresStmt = $pdo->prepare("SELECT * FROM car_features WHERE car_id = ?");
$featuresStmt->execute([$carId]);
$features = $featuresStmt->fetchAll(PDO::FETCH_ASSOC);

// Get car categories
$categoriesStmt = $pdo->prepare("
    SELECT cat.name, cat.slug 
    FROM car_categories cc
    JOIN categories cat ON cc.category_id = cat.id
    WHERE cc.car_id = ?
");
$categoriesStmt->execute([$carId]);
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $car['manufacturer_name'] . " " . $car['model'];
require_once 'templates/header.php';
?>

<div class="container">
    <!-- Основная секция с фото и характеристиками -->
    <div class="vehicle-section">
        <div class="vehicle-image">
            <img src="<?= htmlspecialchars($car['main_image_url']) ?>" alt="<?= htmlspecialchars($car['manufacturer_name'] . ' ' . $car['model']) ?>">
        </div>
        <div class="vehicle-specs">
            <h1><?= htmlspecialchars($car['manufacturer_name'] . ' ' . $car['model'] . ' ' . $car['year']) ?></h1>
            <table>
                <tr>
                    <td>Год выпуска:</td>
                    <td><?= htmlspecialchars($car['year']) ?></td>
                </tr>
                <tr>
                    <td>Тип кузова:</td>
                    <td><?= htmlspecialchars($car['body_type']) ?></td>
                </tr>
                <tr>
                    <td>Тип двигателя:</td>
                    <td><?= htmlspecialchars($car['engine_type']) ?></td>
                </tr>
                <tr>
                    <td>Мощность:</td>
                    <td><?= htmlspecialchars($car['power_hp']) ?> л.с.</td>
                </tr>
                <tr>
                    <td>Разгон 0-100 км/ч:</td>
                    <td><?= htmlspecialchars($car['acceleration_0_100']) ?> сек</td>
                </tr>
                <tr>
                    <td>Макс. скорость:</td>
                    <td><?= htmlspecialchars($car['top_speed_kmh']) ?> км/ч</td>
                </tr>
                <?php if ($car['engine_type'] === 'Электрический'): ?>
                <tr>
                    <td>Ёмкость батареи:</td>
                    <td><?= htmlspecialchars($car['battery_capacity_kwh']) ?> кВт·ч</td>
                </tr>
                <tr>
                    <td>Запас хода:</td>
                    <td><?= htmlspecialchars($car['range_km']) ?> км</td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Цена:</td>
                    <td>$<?= number_format($car['price'], 2) ?></td>
                </tr>
                <?php foreach ($features as $feature): ?>
                <tr>
                    <td><?= htmlspecialchars($feature['feature_name']) ?>:</td>
                    <td><?= htmlspecialchars($feature['feature_value']) ?></td>
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
                <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($image['alt_text'] ?: $car['manufacturer_name'] . ' ' . $car['model']) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Описание -->
    <div class="vehicle-description">
        <p><?= htmlspecialchars($car['description']) ?></p>
        
        <?php if (!empty($categories)): ?>
        <h2>Категории:</h2>
        <div class="categories">
            <?php foreach ($categories as $category): ?>
                <span class="category-tag"><?= htmlspecialchars($category['name']) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>