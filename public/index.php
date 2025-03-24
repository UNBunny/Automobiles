<?php
require_once 'config.php';
require_once 'templates/header.php';

// По умолчанию выбираем категорию "electric"
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : 'electric';
$pageTitle = "Electric Cars";

// Получаем название выбранной категории
$stmtCat = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
$stmtCat->execute([$selectedCategory]);
$currentCategory = $stmtCat->fetch();
?>

<div class="container py-8">
    <h2 class="text-2xl font-bold mb-6">Популярные категории</h2>
    <div class="button-container">
        <button class="scroll-button left" onclick="scrollCategories(-1)">&#10094;</button>
        <div class="button-wrapper" id="category-wrapper">
            <?php
            $stmt = $pdo->query("SELECT * FROM categories");
            while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $isActive = ($selectedCategory === $category['slug']) ? 'active' : '';
                echo '<a href="?category=' . $category['slug'] . '" class="category-button ' . $isActive . ' no-underline">' . 
                     escape($category['name']) . '</a>';
            }
            ?>
        </div>
        <button class="scroll-button right" onclick="scrollCategories(1)">&#10095;</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-6">
        <?php
        $query = "
            SELECT c.*, m.name as manufacturer_name 
            FROM cars c
            JOIN manufacturers m ON c.manufacturer_id = m.id
            JOIN car_categories cc ON c.id = cc.car_id 
            JOIN categories cat ON cc.category_id = cat.id
            WHERE cat.slug = :category
            ORDER BY c.created_at DESC 
            LIMIT 10
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':category', $selectedCategory);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            while ($car = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="card">
                    <div class="image-container">
                        <img src="' . escape($car['main_image_url']) . '" alt="' . 
                        escape($car['manufacturer_name']) . ' ' . escape($car['model']) . '" />
                    </div>
                    <div class="p-4">
                        <h3>' . escape($car['manufacturer_name'] . ' ' . escape($car['model'])) . '</h3>
                        <a href="/car-details.php?id=' . $car['id'] . '" class="text-left view-button">Смотреть</a>
                    </div>
                </div>';
            }
        } else {
            echo '<p class="col-span-full text-center py-8">Нет автомобилей в выбранной категории</p>';
        }
        ?>
    </div>

    <div class="mt-6">
        <a href="/category.php?category=<?= $selectedCategory ?>" class="text-blue-500 font-semibold hover:underline no-underline">
            Смотреть все <?= escape($currentCategory['name'] ?? 'электрические') ?> автомобили
        </a>
        <span class="mx-2">|</span>
        <a href="/cars.php" class="text-blue-500 font-semibold hover:underline no-underline">Смотреть все автомобили</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>