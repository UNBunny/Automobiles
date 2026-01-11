<?php
require_once 'bootstrap.php';

// По умолчанию выбираем категорию "electric"
$selectedCategory = $_GET['category'] ?? 'electric';
$pageTitle = "Electric Cars";

// Используем модели
$carModel = new CarModel();
$categoryModel = new CategoryModel();

// Получаем данные категории
$currentCategory = $categoryModel->getBySlug($selectedCategory);
$allCategories = $categoryModel->getAll();

require_once 'templates/header.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <span class="breadcrumbs-current">Главная</span>
</div>

<div class="container py-8">
    <h2 class="text-2xl font-bold mb-6">Популярные категории</h2>
    <div class="button-container">
        <button class="scroll-button left" onclick="scrollCategories(-1)">&#10094;</button>
        <div class="button-wrapper" id="category-wrapper">
            <?php foreach ($allCategories as $category): ?>
                <?php $isActive = ($selectedCategory === $category['slug']) ? 'active' : ''; ?>
                <a href="?category=<?= $category['slug'] ?>" class="category-button <?= $isActive ?> no-underline">
                    <?= Utils::escape($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="scroll-button right" onclick="scrollCategories(1)">&#10095;</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-6">
        <?php
        $filters = ['category' => $selectedCategory];
        $cars = $carModel->getAll($filters, 'newest', 10);
        
        if (!empty($cars)):
            foreach ($cars as $car):
        ?>
                <div class="card card-hover">
                    <div class="image-container">
                        <img src="<?= Utils::escape($car['main_image_url']) ?>" 
                             alt="<?= Utils::escape($car['manufacturer_name'] . ' ' . $car['model']) ?>" />
                    </div>
                    <div class="p-4">
                        <h3><?= Utils::escape($car['manufacturer_name'] . ' ' . $car['model']) ?></h3>
                        <a href="/car-details.php?id=<?= $car['id'] ?>" class="text-left view-button">Смотреть</a>
                    </div>
                </div>
        <?php 
            endforeach;
        else:
        ?>
            <p class="col-span-full text-center py-8">Нет автомобилей в выбранной категории</p>
        <?php endif; ?>
    </div>

    <div class="mt-6">
        <a href="/category.php?category=<?= $selectedCategory ?>" class="text-blue-500 font-semibold hover:underline no-underline">
            Смотреть все <?= Utils::escape($currentCategory['name'] ?? 'электрические') ?> автомобили
        </a>
        <span class="mx-2">|</span>
        <a href="/cars.php" class="text-blue-500 font-semibold hover:underline no-underline">Смотреть все автомобили</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>