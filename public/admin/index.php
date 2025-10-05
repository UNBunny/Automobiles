<?php
require_once 'config.php';
checkAuth();

$title = 'Дашборд';

// Используем модели для получения статистики
$carModel = new CarModel();
$manufacturerModel = new ManufacturerModel();
$categoryModel = new CategoryModel();

$db = Database::getInstance();
$stats = [
    'cars' => $carModel->count(),
    'manufacturers' => $manufacturerModel->count(),
    'categories' => $categoryModel->count(),
    'views' => $db->fetchOne("SELECT SUM(views) as total FROM cars")['total'] ?? 0
];

// Получаем данные для дашборда
$recent_cars = $carModel->getRecent(5);
$popular_cars = $carModel->getPopular(5);

require_once 'header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo number_format($stats['cars']); ?></h3>
        <p>Автомобилей</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($stats['manufacturers']); ?></h3>
        <p>Производителей</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($stats['categories']); ?></h3>
        <p>Категорий</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($stats['views']); ?></h3>
        <p>Просмотров</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card">
        <h3 style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus-circle" style="color: #2ecc71;"></i>
            Последние добавленные автомобили
        </h3>
        
        <?php if (empty($recent_cars)): ?>
            <p style="text-align: center; color: #666; padding: 40px;">Нет автомобилей</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Изображение</th>
                            <th>Автомобиль</th>
                            <th>Год</th>
                            <th>Цена</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cars as $car): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo escape($car['main_image_url']); ?>" 
                                         alt="<?php echo escape($car['model']); ?>" 
                                         class="image-preview">
                                </td>
                                <td>
                                    <strong><?php echo Utils::escape($car['manufacturer_name']); ?></strong><br>
                                    <?php echo Utils::escape($car['model']); ?>
                                </td>
                                <td><?php echo Utils::escape($car['year']); ?></td>
                                <td>
                                    <?php if ($car['price']): ?>
                                        $<?php echo Utils::formatNumber($car['price']); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo Utils::formatDate($car['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="cars.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Посмотреть все
            </a>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-fire" style="color: #e74c3c;"></i>
            Популярные автомобили
        </h3>
        
        <?php if (empty($popular_cars)): ?>
            <p style="text-align: center; color: #666; padding: 40px;">Нет автомобилей</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Изображение</th>
                            <th>Автомобиль</th>
                            <th>Год</th>
                            <th>Просмотры</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popular_cars as $car): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo escape($car['main_image_url']); ?>" 
                                         alt="<?php echo escape($car['model']); ?>" 
                                         class="image-preview">
                                </td>
                                <td>
                                    <strong><?php echo escape($car['manufacturer_name']); ?></strong><br>
                                    <?php echo escape($car['model']); ?>
                                </td>
                                <td><?php echo escape($car['year']); ?></td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo number_format($car['views']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3 style="margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-plus" style="color: #2ecc71;"></i>
        Быстрые действия
    </h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <a href="cars.php?action=add" class="btn btn-success" style="padding: 15px; text-align: center; text-decoration: none;">
            <i class="fas fa-car"></i><br>
            Добавить автомобиль
        </a>
        <a href="manufacturers.php?action=add" class="btn btn-primary" style="padding: 15px; text-align: center; text-decoration: none;">
            <i class="fas fa-industry"></i><br>
            Добавить производителя
        </a>
        <a href="categories.php?action=add" class="btn btn-warning" style="padding: 15px; text-align: center; text-decoration: none;">
            <i class="fas fa-tags"></i><br>
            Добавить категорию
        </a>
        <a href="../index.php" target="_blank" class="btn btn-primary" style="padding: 15px; text-align: center; text-decoration: none;">
            <i class="fas fa-external-link-alt"></i><br>
            Посмотреть сайт
        </a>
    </div>
</div>

<?php require_once 'footer.php'; ?>