<?php
require_once 'bootstrap.php';

$selectedCategory = $_GET['category'] ?? 'electric';
$sort = $_GET['sort'] ?? 'newest';
$pageTitle = "Electric Cars";

// Используем модели
$carModel = new CarModel();
$categoryModel = new CategoryModel();

// Получаем данные категории
$currentCategory = $categoryModel->getBySlug($selectedCategory);
if (!$currentCategory) {
    Utils::redirect('/');
}

$pageTitle = $currentCategory['name'] . " - Автомобили";

// Получаем автомобили категории
$filters = ['category' => $selectedCategory];
$cars = $carModel->getAll($filters, $sort, 10);

// Формируем URL для сортировки
$baseUrl = '?' . http_build_query(['category' => $selectedCategory]);

require_once 'templates/header.php';
?>

<div class="container py-8">
    <div class="flex justify-between items-center mb-6 mt-8">
        <h2 class="text-2xl font-bold"><?= escape($currentCategory['name'] ?? 'Электрические') ?> автомобили</h2>
        
        <div class="sorting">
            <span class="text-gray-700 sort-label">Сортировать по</span>
            <select class="sort-select ml-2 p-2 border border-gray-300 rounded" 
                    onchange="window.location.href='<?= $baseUrl ?>&sort='+this.value">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Новее по году выпуска</option>
                <option value="year_asc" <?= $sort === 'year_asc' ? 'selected' : '' ?>>Старше по году выпуска</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена по возрастанию</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена по убыванию</option>
                <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Популярность</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <?php if (!empty($cars)): ?>
            <?php foreach ($cars as $car): ?>
                <div class="custom-card" onclick="window.location.href='/car/<?= $car['id'] ?>'">
                    <button class="favorite-button" onclick="toggleFavorite(event)">♡</button>
                    <div class="custom-image-container">
                        <img src="<?= escape($car['main_image_url']) ?>" 
                             alt="<?= escape($car['manufacturer_name'] . ' ' . $car['model']) ?>" 
                             loading="lazy">
                    </div>
                    <div class="custom-content">
                        <h3 class="custom-title"><?= escape($car['year'] . ' ' . $car['manufacturer_name'] . ' ' . $car['model']) ?></h3>
                        <p class="custom-description"><?= escape($car['description']) ?></p>
                        <div class="custom-details">
                            <div class="custom-detail">
                                <span class="custom-icon">🔋</span> <?= escape($car['battery_capacity_kwh']) ?> kWh
                            </div>
                            <div class="custom-detail">
                                <span class="custom-icon">⚡</span> <?= escape($car['power_hp']) ?> л.с.
                            </div>
                            <div class="custom-detail">
                                <span class="custom-icon">🚗</span> <?= escape($car['range_km']) ?> км
                            </div>
                        </div>
                        <p class="custom-price">$<?= number_format($car['price'], 2) ?></p>
                        <button class="custom-view-button">Смотреть описание</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-full text-center py-8">Нет автомобилей в выбранной категории</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>