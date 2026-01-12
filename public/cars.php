<?php
require_once 'bootstrap.php';

// Отправляем Last-Modified заголовок
sendLastModified();

$pageTitle = "Все автомобили";
$pageDescription = "Полный каталог автомобилей с фильтрами по производителю, категории и году выпуска. Найдите свой идеальный автомобиль среди сотен моделей.";

// Получение фильтров
$filters = [
    'category' => $_GET['category'] ?? null,
    'manufacturer' => $_GET['manufacturer'] ?? null,
    'year_from' => isset($_GET['year_from']) ? (int)$_GET['year_from'] : null,
    'year_to' => isset($_GET['year_to']) ? (int)$_GET['year_to'] : null,
    'search' => $_GET['search'] ?? null
];

// Удаляем пустые фильтры
$filters = array_filter($filters, function($value) {
    return $value !== null && $value !== '';
});

$sort = $_GET['sort'] ?? 'newest';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;

// Определяем переменные фильтров для использования в HTML
$manufacturerFilter = $filters['manufacturer'] ?? null;
$categoryFilter = $filters['category'] ?? null;
$filterUrl = 'cars.php';

// Используем модель
$carModel = new CarModel();
$manufacturerModel = new ManufacturerModel();
$categoryModel = new CategoryModel();

// Получаем данные
$totalCars = $carModel->count($filters);
$pagination = Utils::paginate($totalCars, $perPage, $page);
$cars = $carModel->getAll($filters, $sort, $perPage, $pagination['offset']);

// Получаем всех производителей для фильтра
$allManufacturers = $manufacturerModel->getAll();

require_once 'templates/header.php';

// Формируем URL для форм
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');
$queryParams = $_GET;
unset($queryParams['sort']);
$filterUrl = $currentUrl . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');

// Определяем есть ли активные фильтры
$hasActiveFilters = !empty($filters);
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/" title="Главная страница">Главная</a>
    <span class="breadcrumbs-separator">/</span>
    <span class="breadcrumbs-current">Все автомобили</span>
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
      "name": "Все автомобили"
    }
  ]
}
</script>

<div class="container py-8">

<?php if ($hasActiveFilters): ?>
<!-- Активные фильтры -->
<div class="active-filters">
    <?php if (isset($filters['category'])): 
        $category = $categoryModel->getBySlug($filters['category']);
    ?>
        <span class="filter-tag">
            Категория: <?= Utils::escape($category['name'] ?? $filters['category']) ?>
            <button class="filter-tag-remove" onclick="removeFilter('category')" title="Удалить фильтр">×</button>
        </span>
    <?php endif; ?>
    
    <?php if (isset($filters['manufacturer'])): 
        $manufacturer = $manufacturerModel->getBySlug($filters['manufacturer']);
    ?>
        <span class="filter-tag">
            Производитель: <?= Utils::escape($manufacturer['name'] ?? $filters['manufacturer']) ?>
            <button class="filter-tag-remove" onclick="removeFilter('manufacturer')" title="Удалить фильтр">×</button>
        </span>
    <?php endif; ?>
    
    <?php if (isset($filters['year_from']) || isset($filters['year_to'])): ?>
        <span class="filter-tag">
            Год: <?= isset($filters['year_from']) ? $filters['year_from'] : '—' ?> - <?= isset($filters['year_to']) ? $filters['year_to'] : '—' ?>
            <button class="filter-tag-remove" onclick="removeFilter('year_from'); removeFilter('year_to');" title="Удалить фильтр">×</button>
        </span>
    <?php endif; ?>
    
    <?php if (isset($filters['search'])): ?>
        <span class="filter-tag">
            Поиск: "<?= Utils::escape($filters['search']) ?>"
            <button class="filter-tag-remove" onclick="removeFilter('search')" title="Удалить фильтр">×</button>
        </span>
    <?php endif; ?>
    
    <button class="clear-filters-btn" onclick="clearAllFilters()">Сбросить все фильтры</button>
</div>
<?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            <?php 
            if (!empty($filters['manufacturer'])) {
                $manufacturer = $manufacturerModel->getBySlug($filters['manufacturer']);
                echo "Все модели " . Utils::escape($manufacturer['name'] ?? 'Неизвестный производитель');
            } elseif (!empty($filters['category'])) {
                $category = $categoryModel->getBySlug($filters['category']);
                echo Utils::escape($category['name'] ?? 'Неизвестная категория') . " автомобили";
            } else {
                echo "Все автомобили ({$totalCars})";
            }
            ?>
        </h2>

        <div class="filters-container">
            <!-- Фильтр по производителю -->
            <div class="manufacturer-filter">
                <form method="get" action="cars.php" class="manufacturer-form" role="search" aria-label="Фильтр по производителю">
                    <?php if ($categoryFilter): ?>
                        <input type="hidden" name="category" value="<?= $categoryFilter ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['year_from'])): ?>
                        <input type="hidden" name="year_from" value="<?= $_GET['year_from'] ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['year_to'])): ?>
                        <input type="hidden" name="year_to" value="<?= $_GET['year_to'] ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['sort'])): ?>
                        <input type="hidden" name="sort" value="<?= $_GET['sort'] ?>">
                    <?php endif; ?>
                    
                    <label for="manufacturer-select" class="filter-label">Производитель</label>
                    <select id="manufacturer-select" name="manufacturer" class="manufacturer-select" 
                            onchange="this.form.submit()" aria-label="Выберите производителя">
                        <option value="">Все производители</option>
                        <?php foreach ($allManufacturers as $manufacturer): ?>
                            <option value="<?= $manufacturer['slug'] ?>" 
                                    <?= ($manufacturerFilter === $manufacturer['slug']) ? 'selected' : '' ?>>
                                <?= Utils::escape($manufacturer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <form method="get" action="<?= $filterUrl ?>" class="year-filter" role="search" aria-label="Фильтр по году выпуска">
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
                               placeholder="2000" min="2000" max="2030" value="<?= $yearFrom ?>"
                               aria-label="Год выпуска от">
                    </div>
                    <div class="year-input-group">
                        <label for="year-to" class="year-label">до</label>
                        <input type="number" id="year-to" name="year_to" class="year-input" 
                               placeholder="2025" min="2000" max="2030" value="<?= $yearTo ?>"
                               aria-label="Год выпуска до">
                    </div>
                </div>
                <button type="submit" class="apply-button" aria-label="Применить фильтр по году">Применить</button>
            </form>
        
            <div class="sorting">
                <label for="sort-select" class="text-gray-700 sort-label">Сортировать по</label>
                <select id="sort-select" class="sort-select ml-2 p-2 border border-gray-300 rounded" 
                        onchange="window.location.href=this.value" aria-label="Сортировка автомобилей">
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
        <?php if (!empty($cars)): ?>
            <?php foreach ($cars as $car): ?>
                <div class="custom-card" onclick="window.location.href='/car/<?= $car['id'] ?>'">
                    <button class="favorite-button" onclick="toggleFavorite(event)">♡</button>
                    <div class="custom-image-container">
                        <img src="<?= Utils::escape($car['main_image_url']) ?>" 
                             alt="<?= Utils::escape($car['manufacturer_name'] . ' ' . $car['model']) ?>" 
                             loading="lazy">
                    </div>
                    <div class="custom-content">
                        <h3 class="custom-title"><?= Utils::escape($car['year'] . ' ' . $car['manufacturer_name'] . ' ' . $car['model']) ?></h3>
                        <p class="custom-description"><?= Utils::escape(substr($car['description'] ?? '', 0, 100)) ?></p>
                        <div class="custom-details">
                            <?php if ($car['battery_capacity_kwh']): ?>
                                <div class="custom-detail">
                                    <span class="custom-icon">🔋</span> <?= Utils::escape($car['battery_capacity_kwh']) ?> kWh
                                </div>
                            <?php endif; ?>
                            <?php if ($car['power_hp']): ?>
                                <div class="custom-detail">
                                    <span class="custom-icon">⚡</span> <?= Utils::escape($car['power_hp']) ?> л.с.
                                </div>
                            <?php endif; ?>
                            <?php if ($car['range_km']): ?>
                                <div class="custom-detail">
                                    <span class="custom-icon">🚗</span> <?= Utils::escape($car['range_km']) ?> км
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($car['price']): ?>
                            <p class="custom-price">$<?= Utils::formatNumber($car['price']) ?></p>
                        <?php endif; ?>
                        <button class="custom-view-button" onclick="window.location.href='/car/<?= $car['id'] ?>'">Смотреть описание</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-8">
                <p class="text-lg">Автомобили не найдены</p>
                <?php if (!empty($filters)): ?>
                    <p class="text-sm text-gray-600 mt-2">Попробуйте изменить критерии поиска</p>
                    <a href="cars.php" class="text-blue-500 hover:underline">Показать все автомобили</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination-container">
            <nav class="pagination">
                <?php if ($pagination['has_prev']): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $pagination['current_page'] - 1])) ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">← Предыдущая</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>" 
                       class="px-3 py-2 <?= $i === $pagination['current_page'] ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> rounded">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                    <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $pagination['current_page'] + 1])) ?>" 
                       class="px-3 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Следующая →</a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>
</div>

<?php require_once 'templates/footer.php'; ?>