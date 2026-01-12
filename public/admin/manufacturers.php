<?php
require_once 'config.php';
checkAuth();

$title = 'Управление производителями';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$message = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Попробуйте еще раз.';
    } else {
        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $founded_year = $_POST['founded_year'] ? (int)$_POST['founded_year'] : null;
            $country = trim($_POST['country'] ?? '');
            $slug = createSlug($name);

            if (empty($name)) {
                $error = 'Название производителя обязательно';
            } else {
                try {
                    if ($action === 'add') {
                        // Загрузка логотипа
                        $logo_url = '';
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            $logo_url = uploadImage($_FILES['logo']);
                            if (!$logo_url) {
                                $error = 'Ошибка загрузки логотипа';
                            }
                        }

                        if (!$error) {
                            $stmt = $pdo->prepare("
                                INSERT INTO manufacturers (name, logo_url, description, founded_year, country, slug) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$name, $logo_url, $description, $founded_year, $country, $slug]);
                            $message = 'Производитель успешно добавлен';
                            $action = 'list';
                        }
                    } elseif ($action === 'edit' && $id) {
                        // Обновление логотипа
                        $logo_update = '';
                        $logo_params = [];
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                            $logo_url = uploadImage($_FILES['logo']);
                            if ($logo_url) {
                                $logo_update = ', logo_url = ?';
                                $logo_params[] = $logo_url;
                            }
                        }

                        $stmt = $pdo->prepare("
                            UPDATE manufacturers SET 
                                name = ?, description = ?, founded_year = ?, country = ?, slug = ?
                                $logo_update
                            WHERE id = ?
                        ");
                        
                        $params = [$name, $description, $founded_year, $country, $slug];
                        $params = array_merge($params, $logo_params);
                        $params[] = $id;
                        
                        $stmt->execute($params);
                        $message = 'Производитель успешно обновлен';
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'unique') !== false) {
                        $error = 'Производитель с таким названием уже существует';
                    } else {
                        $error = 'Ошибка базы данных: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

// Удаление
if ($action === 'delete' && $id) {
    try {
        // Проверяем, есть ли автомобили этого производителя
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cars WHERE manufacturer_id = ?");
        $stmt->execute([$id]);
        $car_count = $stmt->fetch()['count'];

        if ($car_count > 0) {
            $error = "Нельзя удалить производителя, у которого есть автомобили ($car_count шт.)";
        } else {
            $pdo->prepare("DELETE FROM manufacturers WHERE id = ?")->execute([$id]);
            $message = 'Производитель успешно удален';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
    $action = 'list';
}

// Получение данных для редактирования
$manufacturer = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM manufacturers WHERE id = ?");
    $stmt->execute([$id]);
    $manufacturer = $stmt->fetch();
    
    if (!$manufacturer) {
        $error = 'Производитель не найден';
        $action = 'list';
    }
}

// Получение списка производителей с пагинацией
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where_clause = '';
$params = [];
if (!empty($search)) {
    $where_clause = "WHERE (name ILIKE ? OR country ILIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total_query = "SELECT COUNT(*) as total FROM manufacturers $where_clause";
$stmt = $pdo->prepare($total_query);
$stmt->execute($params);
$total_manufacturers = $stmt->fetch()['total'];
$total_pages = ceil($total_manufacturers / $limit);

$manufacturers_query = "
    SELECT m.*, 
           (SELECT COUNT(*) FROM cars WHERE manufacturer_id = m.id) as car_count
    FROM manufacturers m
    $where_clause
    ORDER BY m.name
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($manufacturers_query);
$stmt->execute($params);
$manufacturers = $stmt->fetchAll();

require_once 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo escape($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?php echo escape($error); ?>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3><i class="fas fa-industry"></i> Список производителей (<?php echo $total_manufacturers; ?>)</h3>
            <a href="?action=add" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить производителя
            </a>
        </div>

        <div class="search-box">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="list">
                <input type="text" name="search" placeholder="Поиск по названию или стране..." 
                       value="<?php echo escape($search); ?>" style="flex: 1; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Найти
                </button>
                <?php if ($search): ?>
                    <a href="?action=list" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Сбросить
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($manufacturers)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                <?php echo $search ? 'Производители не найдены' : 'Нет производителей'; ?>
            </p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Логотип</th>
                            <th>Название</th>
                            <th>Страна</th>
                            <th>Год основания</th>
                            <th>Автомобилей</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($manufacturers as $manufacturer): ?>
                            <tr>
                                <td>
                                    <?php if ($manufacturer['logo_url']): ?>
                                        <img src="<?php echo escape($manufacturer['logo_url']); ?>" 
                                             alt="<?php echo escape($manufacturer['name']); ?>" 
                                             class="image-preview">
                                    <?php else: ?>
                                        <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 8px; color: white; font-weight: bold; font-size: 1.2rem;">
                                            <?php echo strtoupper(substr($manufacturer['name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo escape($manufacturer['name']); ?></strong>
                                    <?php if ($manufacturer['description']): ?>
                                        <br><small style="color: #666;"><?php echo escape(substr($manufacturer['description'], 0, 50)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape($manufacturer['country']); ?></td>
                                <td><?php echo $manufacturer['founded_year'] ? escape($manufacturer['founded_year']) : '-'; ?></td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $manufacturer['car_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="?action=edit&id=<?php echo $manufacturer['id']; ?>" class="btn btn-warning btn-sm">
                                            Редактировать
                                        </a>
                                        <?php if ($manufacturer['car_count'] == 0): ?>
                                            <a href="?action=delete&id=<?php echo $manufacturer['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirmDelete('Вы уверены, что хотите удалить этого производителя?')">
                                                Удалить
                                            </a>
                                        <?php else: ?>
                                            <span class="btn btn-danger btn-sm" style="opacity: 0.5; cursor: not-allowed;" title="Нельзя удалить - есть автомобили">
                                                Удалить
                                            </span>
                                        <?php endif; ?>
                                        <a href="../manufacturers.php" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
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
                            <i class="fas fa-chevron-left"></i> Предыдущая
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
                            Следующая <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <div class="card">
        <h3>
            <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i>
            <?php echo $action === 'add' ? 'Добавить производителя' : 'Редактировать производителя'; ?>
        </h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo escape($manufacturer['name'] ?? ''); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="country">Страна</label>
                    <input type="text" id="country" name="country" class="form-control" 
                           value="<?php echo escape($manufacturer['country'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="founded_year">Год основания</label>
                    <input type="number" id="founded_year" name="founded_year" class="form-control" 
                           min="1800" max="<?php echo date('Y'); ?>"
                           value="<?php echo escape($manufacturer['founded_year'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="logo">Логотип</label>
                <input type="file" id="logo" name="logo" 
                       class="form-control form-control-file" 
                       accept="image/*" onchange="previewImage(this, 'logo-preview')">
                <?php if ($manufacturer && $manufacturer['logo_url']): ?>
                    <img id="logo-preview" src="<?php echo escape($manufacturer['logo_url']); ?>" 
                         class="image-preview" style="margin-top: 10px; display: block;">
                <?php else: ?>
                    <img id="logo-preview" class="image-preview" style="margin-top: 10px; display: none;">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" class="form-control" 
                          rows="4"><?php echo escape($manufacturer['description'] ?? ''); ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="?action=list" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Сохранить
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>