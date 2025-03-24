<?php
require_once 'config.php';
require_once 'templates/header.php';

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'electric';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$pageTitle = "Electric Cars";

// Получаем название выбранной категории
$stmtCat = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
$stmtCat->execute([$selectedCategory]);
$currentCategory = $stmtCat->fetch();

// Формируем базовый запрос
$query = "
    SELECT c.*, m.name as manufacturer_name 
    FROM cars c
    JOIN manufacturers m ON c.manufacturer_id = m.id
    JOIN car_categories cc ON c.id = cc.car_id 
    JOIN categories cat ON cc.category_id = cat.id
    WHERE cat.slug = :category
";

// Добавляем сортировку в зависимости от выбора
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY c.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY c.price DESC";
        break;
    case 'popularity':
        $query .= " ORDER BY c.views DESC";
        break;
    case 'year_asc':
        $query .= " ORDER BY c.year ASC";
        break;
    default: // 'newest'
        $query .= " ORDER BY c.year DESC";
}

$query .= " LIMIT 10";

// Подготавливаем и выполняем запрос
$stmt = $pdo->prepare($query);
$stmt->bindParam(':category', $selectedCategory);
$stmt->execute();

// Формируем URL для сортировки с сохранением других параметров
$baseUrl = '?' . http_build_query(['category' => $selectedCategory]);
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
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($car = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="custom-card" onclick="window.location.href='/car-details.php?id=<?= $car['id'] ?>'">
                    <button class="favorite-button" onclick="toggleFavorite(event)">♡</button>
                    <div class="custom-image-container">
                        <img src="<?= escape($car['main_image_url']) ?>" alt="<?= escape($car['manufacturer_name'] . ' ' . $car['model']) ?>">
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
            <?php endwhile; ?>
        <?php else: ?>
            <p class="col-span-full text-center py-8">Нет автомобилей в выбранной категории</p>
        <?php endif; ?>
    </div>

    <div class="mt-6">
        <a href="/category.php?category=<?= $selectedCategory ?>&sort=<?= $sort ?>" class="text-blue-500 font-semibold hover:underline">
            Смотреть все <?= escape($currentCategory['name'] ?? 'электрические') ?> автомобили
        </a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>