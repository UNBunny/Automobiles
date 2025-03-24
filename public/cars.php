<?php
require_once 'config.php';
require_once 'templates/header.php';

$pageTitle = "Все автомобили";
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;
$manufacturerFilter = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : null;
$yearFrom = isset($_GET['year_from']) ? (int)$_GET['year_from'] : null;
$yearTo = isset($_GET['year_to']) ? (int)$_GET['year_to'] : null;

// Build the query
$query = "
    SELECT c.*, m.name as manufacturer_name, m.logo_url as manufacturer_logo
    FROM cars c
    JOIN manufacturers m ON c.manufacturer_id = m.id
";

$where = [];
$params = [];

if ($categoryFilter) {
    $query .= " JOIN car_categories cc ON c.id = cc.car_id JOIN categories cat ON cc.category_id = cat.id";
    $where[] = "cat.slug = ?";
    $params[] = $categoryFilter;
}

if ($manufacturerFilter) {
    $where[] = "m.slug = ?";
    $params[] = $manufacturerFilter;
}

if ($yearFrom !== null) {
    $where[] = "c.year >= ?";
    $params[] = $yearFrom;
}

if ($yearTo !== null) {
    $where[] = "c.year <= ?";
    $params[] = $yearTo;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
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
    default: // 'newest' and default
        $query .= " ORDER BY c.year DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

// Get current URL without sort parameter for filter form
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
$queryParams = $_GET;
unset($queryParams['sort']);
$filterUrl = $currentUrl . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
?>

<div class="container py-8">


    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            <?php 
            if ($manufacturerFilter) {
                $stmtM = $pdo->prepare("SELECT name FROM manufacturers WHERE slug = ?");
                $stmtM->execute([$manufacturerFilter]);
                $manufacturer = $stmtM->fetch();
                echo "Все модели " . htmlspecialchars($manufacturer['name']);
            } elseif ($categoryFilter) {
                $stmtC = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
                $stmtC->execute([$categoryFilter]);
                $category = $stmtC->fetch();
                echo htmlspecialchars($category['name']) . " автомобили";
            } else {
                echo "Все автомобили";
            }
            ?>
        </h2>

        <div class="filters-container">
            <?php if ($manufacturerFilter): ?>
            <?php endif; ?>

            <form method="get" action="<?= $filterUrl ?>" class="year-filter">
                <?php if ($manufacturerFilter): ?>
                    <input type="hidden" name="manufacturer" value="<?= $manufacturerFilter ?>">
                <?php endif; ?>
                <?php if ($categoryFilter): ?>
                    <input type="hidden" name="category" value="<?= $categoryFilter ?>">
                <?php endif; ?>
                
                <div class="year-range">
                    <div class="year-input-group">
                        <label for="year-from" class="year-label">Год от</label>
                        <input type="number" id="year-from" name="year_from" class="year-input" 
                               placeholder="2000" min="2000" max="2030" value="<?= $yearFrom ?>">
                    </div>
                    <div class="year-input-group">
                        <label for="year-to" class="year-label">до</label>
                        <input type="number" id="year-to" name="year_to" class="year-input" 
                               placeholder="2025" min="2000" max="2030" value="<?= $yearTo ?>">
                    </div>
                </div>
                <button type="submit" class="apply-button">Применить</button>
            </form>
        
            <div class="sorting">
                <span class="text-gray-700 sort-label">Сортировать по</span>
                <select class="sort-select ml-2 p-2 border border-gray-300 rounded" onchange="window.location.href=this.value">
                    <option value="?<?= http_build_query(array_merge($queryParams, ['sort' => 'newest'])) ?>" <?= $sort === 'newest' ? 'selected' : '' ?>>Новее по году выпуска</option>
                    <option value="?<?= http_build_query(array_merge($queryParams, ['sort' => 'year_asc'])) ?>" <?= $sort === 'year_asc' ? 'selected' : '' ?>>Старше по году выпуска</option>
                    <option value="?<?= http_build_query(array_merge($queryParams, ['sort' => 'price_asc'])) ?>" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена по возрастанию</option>
                    <option value="?<?= http_build_query(array_merge($queryParams, ['sort' => 'price_desc'])) ?>" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена по убыванию</option>
                    <option value="?<?= http_build_query(array_merge($queryParams, ['sort' => 'popularity'])) ?>" <?= $sort === 'popularity' ? 'selected' : '' ?>>Популярность</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($car = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="custom-card" onclick="window.location.href='/car-details.php?id=<?= $car['id'] ?>'">
                    <button class="favorite-button" onclick="toggleFavorite(event)">♡</button>
                    <div class="custom-image-container">
                        <img src="<?= htmlspecialchars($car['main_image_url']) ?>" alt="<?= htmlspecialchars($car['manufacturer_name'] . ' ' . $car['model']) ?>">
                    </div>
                    <div class="custom-content">
                        <h3 class="custom-title"><?= htmlspecialchars($car['year'] . ' ' . $car['manufacturer_name'] . ' ' . $car['model']) ?></h3>
                        <p class="custom-description"><?= htmlspecialchars($car['description']) ?></p>
                        <div class="custom-details">
                            <?php if ($car['battery_capacity_kwh']): ?>
                                <div class="custom-detail">
                                    <span class="custom-icon">🔋</span> <?= htmlspecialchars($car['battery_capacity_kwh']) ?> kWh
                                </div>
                            <?php endif; ?>
                            <div class="custom-detail">
                                <span class="custom-icon">⚡</span> <?= htmlspecialchars($car['power_hp']) ?> л.с.
                            </div>
                            <?php if ($car['range_km']): ?>
                                <div class="custom-detail">
                                    <span class="custom-icon">🚗</span> <?= htmlspecialchars($car['range_km']) ?> км
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="custom-price">$<?= number_format($car['price'], 2) ?></p>
                        <button class="custom-view-button" onclick="window.location.href='/car-details.php?id=<?= $car['id'] ?>'">Смотреть описание</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-8">
                <p class="text-lg">Автомобили не найдены</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>