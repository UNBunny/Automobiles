<?php
require_once 'bootstrap.php';

// Отправляем Last-Modified заголовок
sendLastModified();

// По умолчанию выбираем категорию "electric"
$selectedCategory = $_GET['category'] ?? 'electric';
$pageTitle = "Главная";
$pageDescription = "Каталог современных автомобилей: электромобили, гибриды, бензиновые и дизельные авто. Подробные характеристики, фото и описания моделей от ведущих производителей.";

// Используем модели
$carModel = new CarModel();
$categoryModel = new CategoryModel();

// Получаем данные категории
$currentCategory = $categoryModel->getBySlug($selectedCategory);
$allCategories = $categoryModel->getAll();

require_once 'templates/header.php';
?>

<main class="container py-8">
    <section>
        <h2 class="text-2xl font-bold mb-6">Популярные категории</h2>
        <div class="button-container">
            <button class="scroll-button left" onclick="scrollCategories(-1)">&#10094;</button>
            <nav class="button-wrapper" id="category-wrapper" aria-label="Категории автомобилей">
            <?php foreach ($allCategories as $category): ?>
                <?php $isActive = ($selectedCategory === $category['slug']) ? 'active' : ''; ?>
                <a href="?category=<?= $category['slug'] ?>" 
                   class="category-button <?= $isActive ?> no-underline"
                   title="Показать автомобили категории <?= Utils::escape($category['name']) ?>">
                    <?= Utils::escape($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <button class="scroll-button right" onclick="scrollCategories(1)">&#10095;</button>
    </div>
    </section>

    <section aria-label="Популярные автомобили">
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
                        <a href="/car/<?= $car['id'] ?>" 
                           class="text-left view-button"
                           title="Подробная информация о <?= Utils::escape($car['manufacturer_name'] . ' ' . $car['model']) ?>">Смотреть</a>
                    </div>
                </div>
        <?php 
            endforeach;
        else:
        ?>
            <p class="col-span-full text-center py-8">Нет автомобилей в выбранной категории</p>
        <?php endif; ?>
        </div>

        <nav class="mt-6" aria-label="Навигация по категориям">
            <a href="/category.php?category=<?= $selectedCategory ?>" 
           class="text-blue-500 font-semibold hover:underline no-underline"
           title="Полный каталог автомобилей категории <?= Utils::escape($currentCategory['name'] ?? 'электрические') ?>">
            Смотреть все <?= Utils::escape($currentCategory['name'] ?? 'электрические') ?> автомобили
        </a>
        <span class="mx-2">|</span>
        <a href="/cars.php" 
           class="text-blue-500 font-semibold hover:underline no-underline"
           title="Полный каталог всех доступных автомобилей">Смотреть все автомобили</a>
        </nav>
    </section>
</main>

<!-- Schema.org микроразметка для главной страницы -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Automobiles",
  "url": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>",
  "description": "Каталог современных автомобилей с подробными характеристиками",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?= 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] ?>/cars.php?search={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

<?php require_once 'templates/footer.php'; ?>