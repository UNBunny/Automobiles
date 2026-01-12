<?php
require_once 'bootstrap.php';

$errorMessage = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $errorMessage = "Некорректный формат ID автомобиля. Пожалуйста, проверьте ссылку.";
    $pageTitle = "Ошибка - Автомобиль не найден";
    require_once 'templates/header.php';
    ?>
    <div class="error-page">
        <div class="error-code">400</div>
        <h1 class="error-title">Некорректный запрос</h1>
        <p class="error-description"><?= Utils::escape($errorMessage) ?></p>
        <div class="error-actions">
            <a href="/" class="error-button">На главную</a>
            <a href="/cars.php" class="error-button error-button-secondary">Все автомобили</a>
        </div>
    </div>
    <?php
    require_once 'templates/footer.php';
    exit;
}

$carId = (int)$_GET['id'];

// Используем модель
$carModel = new CarModel();
$car = $carModel->getById($carId);

if (!$car) {
    $pageTitle = "Автомобиль не найден";
    require_once 'templates/header.php';
    ?>
    <div class="error-page">
        <div class="error-code">404</div>
        <h1 class="error-title">Автомобиль не найден</h1>
        <p class="error-description">
            Автомобиль с ID <?= $carId ?> не существует или был удален из базы данных.
            Возможно, вы перешли по устаревшей ссылке.
        </p>
        <div class="error-actions">
            <a href="/cars.php" class="error-button">Все автомобили</a>
            <a href="/" class="error-button error-button-secondary">На главную</a>
            <a href="/manufacturers.php" class="error-button error-button-secondary">Производители</a>
        </div>
    </div>
    <?php
    require_once 'templates/footer.php';
    exit;
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
$pageDescription = "Подробная информация о " . ($car['manufacturer_name'] ?? '') . " " . ($car['model'] ?? '') . " (" . ($car['year'] ?? '') . "): технические характеристики, фотографии, особенности модели.";
$ogType = "product";
$ogImage = $car['main_image_url'] ?? '';
require_once 'templates/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/" title="Главная страница">Главная</a>
    <span class="breadcrumbs-separator">/</span>
    <a href="/cars.php" title="Каталог автомобилей">Автомобили</a>
    <span class="breadcrumbs-separator">/</span>
    <span class="breadcrumbs-current"><?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '')) ?></span>
</div>

<!-- Schema.org для Breadcrumbs -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Главная",
      "item": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>/"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Автомобили",
      "item": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>/cars.php"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '')) ?>"
    }
  ]
}
</script>

<!-- Кнопка назад -->
<a href="/cars.php" class="back-button">← Вернуться к списку автомобилей</a>

<div class="container">
    <!-- Основная секция с фото и характеристиками -->
    <div class="vehicle-section">
        <div class="vehicle-image">
            <img src="<?= Utils::escape($car['main_image_url'] ?? "") ?>" 
                 alt="<?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '')) ?>" 
                 loading="lazy">
        </div>
        <div class="vehicle-specs">
            <h1><?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '') . ' ' . ($car['year'] ?? '')) ?></h1>
            <div class="view-counter">
                <span class="material-icons">visibility</span>
                <span><?= number_format($car['views'] ?? 0) ?> просмотров</span>
            </div>
            <table id="specs-table">
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
            <button id="toggle-specs" class="show-more-btn" onclick="toggleSpecs()">Показать все характеристики</button>
        </div>
    </div>

    <?php if (count($images) > 1): ?>
    <div class="additional-images">
        <h2>Дополнительные фото</h2>
        <div class="image-grid">
            <?php foreach ($images as $image): ?>
                <?php if (!$image['is_main']): ?>
                <img src="<?= Utils::escape($image['image_url'] ?? "") ?>" 
                     alt="<?= Utils::escape($image['alt_text'] ?: (($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? ''))) ?>" 
                     loading="lazy">
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

<!-- Schema.org микроразметка для автомобиля -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "<?= Utils::escape(($car['manufacturer_name'] ?? '') . ' ' . ($car['model'] ?? '')) ?>",
  "description": "<?= Utils::escape($car['description'] ?? '') ?>",
  "image": "<?= Utils::escape($car['main_image_url'] ?? '') ?>",
  "brand": {
    "@type": "Brand",
    "name": "<?= Utils::escape($car['manufacturer_name'] ?? '') ?>"
  },
  "offers": {
    "@type": "Offer",
    "availability": "https://schema.org/InStock"
  },
  "additionalProperty": [
    {
      "@type": "PropertyValue",
      "name": "Год выпуска",
      "value": "<?= Utils::escape($car['year'] ?? '') ?>"
    }
    <?php if (!empty($car['engine_type'])): ?>
    ,{
      "@type": "PropertyValue",
      "name": "Тип двигателя",
      "value": "<?= Utils::escape($car['engine_type'] ?? '') ?>"
    }
    <?php endif; ?>
    <?php if (!empty($car['body_type'])): ?>
    ,{
      "@type": "PropertyValue",
      "name": "Тип кузова",
      "value": "<?= Utils::escape($car['body_type'] ?? '') ?>"
    }
    <?php endif; ?>
  ]
}
</script>

<?php require_once 'templates/footer.php'; ?>
