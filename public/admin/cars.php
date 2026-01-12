<?php
require_once 'config.php';
checkAuth();

$title = 'Управление автомобилями';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$message = '';
$error = '';

// Получаем списки для селектов
$manufacturers = $pdo->query("SELECT * FROM manufacturers ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$body_types = $pdo->query("SELECT * FROM body_types ORDER BY name")->fetchAll();
$engine_types = $pdo->query("SELECT * FROM engine_types ORDER BY name")->fetchAll();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Попробуйте еще раз.';
    } else {
        if ($action === 'add' || $action === 'edit') {
            $manufacturer_id = $_POST['manufacturer_id'] ?? '';
            $model = trim($_POST['model'] ?? '');
            $year = (int)($_POST['year'] ?? 0);
            $body_type_id = $_POST['body_type_id'] ?? null;
            $engine_type_id = $_POST['engine_type_id'] ?? null;
            $power_hp = $_POST['power_hp'] ? (int)$_POST['power_hp'] : null;
            $battery_capacity_kwh = $_POST['battery_capacity_kwh'] ? (float)$_POST['battery_capacity_kwh'] : null;
            $range_km = $_POST['range_km'] ? (int)$_POST['range_km'] : null;
            $acceleration_0_100 = $_POST['acceleration_0_100'] ? (float)$_POST['acceleration_0_100'] : null;
            $top_speed_kmh = $_POST['top_speed_kmh'] ? (int)$_POST['top_speed_kmh'] : null;
            $price = $_POST['price'] ? (float)$_POST['price'] : null;
            $description = trim($_POST['description'] ?? '');
            $selected_categories = $_POST['categories'] ?? [];

            // Создаем slug
            $stmt = $pdo->prepare("SELECT name FROM manufacturers WHERE id = ?");
            $stmt->execute([$manufacturer_id]);
            $manufacturer = $stmt->fetch();
            $slug = createSlug($manufacturer['name'] . '-' . $model . '-' . $year);

            if (empty($model) || empty($manufacturer_id) || $year < 1900) {
                $error = 'Заполните обязательные поля';
            } else {
                try {
                    if ($action === 'add') {
                        // Загрузка изображения
                        $main_image_url = '';
                        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                            $main_image_url = uploadImage($_FILES['main_image']);
                            if (!$main_image_url) {
                                $error = 'Ошибка загрузки изображения';
                            }
                        }

                        if (!$error) {
                            $stmt = $pdo->prepare("
                                INSERT INTO cars (manufacturer_id, model, year, body_type_id, engine_type_id, 
                                                power_hp, battery_capacity_kwh, range_km, acceleration_0_100, 
                                                top_speed_kmh, price, main_image_url, description, slug) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                $manufacturer_id, $model, $year, $body_type_id, $engine_type_id,
                                $power_hp, $battery_capacity_kwh, $range_km, $acceleration_0_100,
                                $top_speed_kmh, $price, $main_image_url, $description, $slug
                            ]);

                            $car_id = $pdo->lastInsertId();

                            // Добавляем категории
                            foreach ($selected_categories as $category_id) {
                                $stmt = $pdo->prepare("INSERT INTO car_categories (car_id, category_id) VALUES (?, ?)");
                                $stmt->execute([$car_id, $category_id]);
                            }

                            $message = 'Автомобиль успешно добавлен';
                            $action = 'list';
                        }
                    } elseif ($action === 'edit' && $id) {
                        // Обновление изображения
                        $image_update = '';
                        $image_params = [];
                        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                            $main_image_url = uploadImage($_FILES['main_image']);
                            if ($main_image_url) {
                                $image_update = ', main_image_url = ?';
                                $image_params[] = $main_image_url;
                            }
                        }

                        $stmt = $pdo->prepare("
                            UPDATE cars SET 
                                manufacturer_id = ?, model = ?, year = ?, body_type_id = ?, engine_type_id = ?,
                                power_hp = ?, battery_capacity_kwh = ?, range_km = ?, acceleration_0_100 = ?,
                                top_speed_kmh = ?, price = ?, description = ?, slug = ?, updated_at = CURRENT_TIMESTAMP
                                $image_update
                            WHERE id = ?
                        ");
                        
                        $params = [
                            $manufacturer_id, $model, $year, $body_type_id, $engine_type_id,
                            $power_hp, $battery_capacity_kwh, $range_km, $acceleration_0_100,
                            $top_speed_kmh, $price, $description, $slug
                        ];
                        $params = array_merge($params, $image_params);
                        $params[] = $id;
                        
                        $stmt->execute($params);

                        // Обновляем категории
                        $pdo->prepare("DELETE FROM car_categories WHERE car_id = ?")->execute([$id]);
                        foreach ($selected_categories as $category_id) {
                            $stmt = $pdo->prepare("INSERT INTO car_categories (car_id, category_id) VALUES (?, ?)");
                            $stmt->execute([$id, $category_id]);
                        }

                        $message = 'Автомобиль успешно обновлен';
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    $error = 'Ошибка базы данных: ' . $e->getMessage();
                }
            }
        }
    }
}

// Удаление
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM car_categories WHERE car_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM car_features WHERE car_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM car_images WHERE car_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM cars WHERE id = ?")->execute([$id]);
        $message = 'Автомобиль успешно удален';
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
    $action = 'list';
}

// Получение данных для редактирования
$car = null;
$car_categories = [];
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();
    
    if ($car) {
        $stmt = $pdo->prepare("SELECT category_id FROM car_categories WHERE car_id = ?");
        $stmt->execute([$id]);
        $car_categories = array_column($stmt->fetchAll(), 'category_id');
    } else {
        $error = 'Автомобиль не найден';
        $action = 'list';
    }
}

// Получение списка автомобилей с пагинацией
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where_clause = '';
$params = [];
if (!empty($search)) {
    $where_clause = "WHERE (c.model ILIKE ? OR m.name ILIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total_query = "SELECT COUNT(*) as total FROM cars c JOIN manufacturers m ON c.manufacturer_id = m.id $where_clause";
$stmt = $pdo->prepare($total_query);
$stmt->execute($params);
$total_cars = $stmt->fetch()['total'];
$total_pages = ceil($total_cars / $limit);

$cars_query = "
    SELECT c.*, m.name as manufacturer_name 
    FROM cars c
    JOIN manufacturers m ON c.manufacturer_id = m.id
    $where_clause
    ORDER BY c.created_at DESC 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($cars_query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?php echo escape($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?php echo escape($error); ?>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Список автомобилей (<?php echo $total_cars; ?>)</h3>
            <a href="?action=add" class="btn btn-success">
                Добавить автомобиль
            </a>
        </div>

        <div class="search-box">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="list">
                <input type="text" name="search" placeholder="Поиск по модели или производителю..." 
                       value="<?php echo escape($search); ?>" style="flex: 1; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                <button type="submit" class="btn btn-primary">
                    Найти
                </button>
                <?php if ($search): ?>
                    <a href="?action=list" class="btn btn-secondary">
                        Сбросить
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($cars)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                <?php echo $search ? 'Автомобили не найдены' : 'Нет автомобилей'; ?>
            </p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Изображение</th>
                            <th>Автомобиль</th>
                            <th>Год</th>
                            <th>Цена</th>
                            <th>Просмотры</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td>
                                    <?php if ($car['main_image_url']): ?>
                                        <img src="<?php echo escape($car['main_image_url']); ?>" 
                                             alt="<?php echo escape($car['model']); ?>" 
                                             class="image-preview">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 5px;">
                                            <i class="fas fa-image" style="color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo escape($car['manufacturer_name']); ?></strong><br>
                                    <?php echo escape($car['model']); ?>
                                </td>
                                <td><?php echo escape($car['year']); ?></td>
                                <td>
                                    <?php if ($car['price']): ?>
                                        $<?php echo number_format($car['price'], 0); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo number_format($car['views']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($car['created_at']); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?action=edit&id=<?php echo $car['id']; ?>" class="btn btn-warning btn-sm">
                                            Редактировать
                                        </a>
                                        <a href="?action=delete&id=<?php echo $car['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirmDelete('Вы уверены, что хотите удалить этот автомобиль?')">
                                            Удалить
                                        </a>
                                        <a href="../car/<?php echo $car['id']; ?>" 
                                           target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                                            Просмотр
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?action=list&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                            Предыдущая
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?action=list&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?action=list&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                            Следующая
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h3>

            <?php echo $action === 'add' ? 'Добавить автомобиль' : 'Редактировать автомобиль'; ?>
        </h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div class="form-group">
                        <label for="manufacturer_id">Производитель *</label>
                        <select id="manufacturer_id" name="manufacturer_id" class="form-control" required>
                            <option value="">Выберите производителя</option>
                            <?php foreach ($manufacturers as $manufacturer): ?>
                                <option value="<?php echo $manufacturer['id']; ?>" 
                                        <?php echo ($car && $car['manufacturer_id'] == $manufacturer['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($manufacturer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="model">Модель *</label>
                        <input type="text" id="model" name="model" class="form-control" 
                               value="<?php echo escape($car['model'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Год *</label>
                        <input type="number" id="year" name="year" class="form-control" 
                               min="1900" max="<?php echo date('Y') + 2; ?>"
                               value="<?php echo escape($car['year'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="body_type_id">Тип кузова</label>
                        <select id="body_type_id" name="body_type_id" class="form-control">
                            <option value="">Не указан</option>
                            <?php foreach ($body_types as $body_type): ?>
                                <option value="<?php echo $body_type['id']; ?>" 
                                        <?php echo ($car && $car['body_type_id'] == $body_type['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($body_type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="engine_type_id">Тип двигателя</label>
                        <select id="engine_type_id" name="engine_type_id" class="form-control">
                            <option value="">Не указан</option>
                            <?php foreach ($engine_types as $engine_type): ?>
                                <option value="<?php echo $engine_type['id']; ?>" 
                                        <?php echo ($car && $car['engine_type_id'] == $engine_type['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($engine_type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Цена (USD)</label>
                        <input type="number" id="price" name="price" class="form-control" 
                               step="0.01" min="0"
                               value="<?php echo escape($car['price'] ?? ''); ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="power_hp">Мощность (л.с.)</label>
                        <input type="number" id="power_hp" name="power_hp" class="form-control" 
                               min="0" value="<?php echo escape($car['power_hp'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="battery_capacity_kwh">Емкость батареи (кВт·ч)</label>
                        <input type="number" id="battery_capacity_kwh" name="battery_capacity_kwh" 
                               class="form-control" step="0.1" min="0"
                               value="<?php echo escape($car['battery_capacity_kwh'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="range_km">Запас хода (км)</label>
                        <input type="number" id="range_km" name="range_km" class="form-control" 
                               min="0" value="<?php echo escape($car['range_km'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="acceleration_0_100">Разгон 0-100 км/ч (сек)</label>
                        <input type="number" id="acceleration_0_100" name="acceleration_0_100" 
                               class="form-control" step="0.1" min="0"
                               value="<?php echo escape($car['acceleration_0_100'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="top_speed_kmh">Максимальная скорость (км/ч)</label>
                        <input type="number" id="top_speed_kmh" name="top_speed_kmh" 
                               class="form-control" min="0"
                               value="<?php echo escape($car['top_speed_kmh'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="main_image">Основное изображение</label>
                        <input type="file" id="main_image" name="main_image" 
                               class="form-control form-control-file" 
                               accept="image/*" onchange="previewImage(this, 'image-preview')">
                        <?php if ($car && $car['main_image_url']): ?>
                            <img id="image-preview" src="<?php echo escape($car['main_image_url']); ?>" 
                                 class="image-preview" style="margin-top: 10px; display: block;">
                        <?php else: ?>
                            <img id="image-preview" class="image-preview" style="margin-top: 10px; display: none;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Категории</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 10px;">
                    <?php foreach ($categories as $category): ?>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
                            <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                                   <?php echo in_array($category['id'], $car_categories) ? 'checked' : ''; ?>>
                            <?php echo escape($category['name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" class="form-control" 
                          rows="4"><?php echo escape($car['description'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="?action=list" class="btn btn-secondary">
                    Отмена
                </a>
                <button type="submit" class="btn btn-success">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>