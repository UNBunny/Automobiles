<?php
require_once 'config.php';
checkAuth();

$title = 'Управление типами кузова';
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
            $slug = trim($_POST['slug'] ?? '') ?: createSlug($name);

            if (empty($name)) {
                $error = 'Название типа кузова обязательно';
            } elseif (empty($slug)) {
                $error = 'Slug обязателен';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO body_types (name, slug) VALUES (?, ?)");
                        $stmt->execute([$name, $slug]);
                        $message = 'Тип кузова успешно добавлен';
                        $action = 'list';
                    } elseif ($action === 'edit' && $id) {
                        $stmt = $pdo->prepare("UPDATE body_types SET name = ?, slug = ? WHERE id = ?");
                        $stmt->execute([$name, $slug, $id]);
                        $message = 'Тип кузова успешно обновлен';
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'unique') !== false) {
                        $error = 'Тип кузова с таким названием или slug уже существует';
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
        // Проверяем, есть ли автомобили с этим типом кузова
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cars WHERE body_type_id = ?");
        $stmt->execute([$id]);
        $car_count = $stmt->fetch()['count'];

        if ($car_count > 0) {
            $error = "Нельзя удалить тип кузова, который используется автомобилями ($car_count шт.)";
        } else {
            $pdo->prepare("DELETE FROM body_types WHERE id = ?")->execute([$id]);
            $message = 'Тип кузова успешно удален';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
    $action = 'list';
}

// Получение данных для редактирования
$body_type = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM body_types WHERE id = ?");
    $stmt->execute([$id]);
    $body_type = $stmt->fetch();
    
    if (!$body_type) {
        $error = 'Тип кузова не найден';
        $action = 'list';
    }
}

// Получение списка типов кузова с пагинацией
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where_clause = '';
$params = [];
if (!empty($search)) {
    $where_clause = "WHERE (name ILIKE ? OR slug ILIKE ?)";
    $params = ["%$search%", "%$search%"];
}

$total_query = "SELECT COUNT(*) as total FROM body_types $where_clause";
$stmt = $pdo->prepare($total_query);
$stmt->execute($params);
$total_body_types = $stmt->fetch()['total'];
$total_pages = ceil($total_body_types / $limit);

$body_types_query = "
    SELECT bt.*, 
           (SELECT COUNT(*) FROM cars WHERE body_type_id = bt.id) as car_count
    FROM body_types bt
    $where_clause
    ORDER BY bt.name
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($body_types_query);
$stmt->execute($params);
$body_types = $stmt->fetchAll();

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
            <h3><i class="fas fa-shapes"></i> Список типов кузова (<?php echo $total_body_types; ?>)</h3>
            <a href="?action=add" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить тип кузова
            </a>
        </div>

        <div class="search-box">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="action" value="list">
                <input type="text" name="search" placeholder="Поиск по названию или slug..." 
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

        <?php if (empty($body_types)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                <?php echo $search ? 'Типы кузова не найдены' : 'Нет типов кузова'; ?>
            </p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Slug</th>
                            <th>Автомобилей</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($body_types as $body_type): ?>
                            <tr>
                                <td><?php echo $body_type['id']; ?></td>
                                <td><strong><?php echo escape($body_type['name']); ?></strong></td>
                                <td>
                                    <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">
                                        <?php echo escape($body_type['slug']); ?>
                                    </code>
                                </td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $body_type['car_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="?action=edit&id=<?php echo $body_type['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($body_type['car_count'] == 0): ?>
                                            <a href="?action=delete&id=<?php echo $body_type['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirmDelete('Вы уверены, что хотите удалить этот тип кузова?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn btn-danger btn-sm" style="opacity: 0.5; cursor: not-allowed;" title="Нельзя удалить - используется автомобилями">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                        <?php endif; ?>
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
            <?php echo $action === 'add' ? 'Добавить тип кузова' : 'Редактировать тип кузова'; ?>
        </h3>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo escape($body_type['name'] ?? ''); ?>" 
                       onkeyup="generateSlug(this.value, 'slug')" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (URL) *</label>
                <input type="text" id="slug" name="slug" class="form-control" 
                       value="<?php echo escape($body_type['slug'] ?? ''); ?>" required>
                <small style="color: #666;">URL-совместимое имя типа кузова (только латинские буквы, цифры и дефисы)</small>
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