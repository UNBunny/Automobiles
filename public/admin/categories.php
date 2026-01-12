<?php
require_once 'config.php';
checkAuth();

$title = 'Управление категориями';
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
                $error = 'Название категории обязательно';
            } elseif (empty($slug)) {
                $error = 'Slug обязателен';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                        $stmt->execute([$name, $slug]);
                        $message = 'Категория успешно добавлена';
                        $action = 'list';
                    } elseif ($action === 'edit' && $id) {
                        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
                        $stmt->execute([$name, $slug, $id]);
                        $message = 'Категория успешно обновлена';
                        $action = 'list';
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'unique') !== false) {
                        $error = 'Категория с таким названием или slug уже существует';
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
        // Проверяем, есть ли автомобили в этой категории
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM car_categories WHERE category_id = ?");
        $stmt->execute([$id]);
        $car_count = $stmt->fetch()['count'];

        if ($car_count > 0) {
            $error = "Нельзя удалить категорию, в которой есть автомобили ($car_count шт.)";
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            $message = 'Категория успешно удалена';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
    $action = 'list';
}

// Получение данных для редактирования
$category = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        $error = 'Категория не найдена';
        $action = 'list';
    }
}

// Получение списка категорий с пагинацией
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

$total_query = "SELECT COUNT(*) as total FROM categories $where_clause";
$stmt = $pdo->prepare($total_query);
$stmt->execute($params);
$total_categories = $stmt->fetch()['total'];
$total_pages = ceil($total_categories / $limit);

$categories_query = "
    SELECT c.*, 
           (SELECT COUNT(*) FROM car_categories WHERE category_id = c.id) as car_count
    FROM categories c
    $where_clause
    ORDER BY c.name
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($categories_query);
$stmt->execute($params);
$categories = $stmt->fetchAll();

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
            <h3><i class="fas fa-tags"></i> Список категорий (<?php echo $total_categories; ?>)</h3>
            <a href="?action=add" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить категорию
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

        <?php if (empty($categories)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                <?php echo $search ? 'Категории не найдены' : 'Нет категорий'; ?>
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
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><strong><?php echo escape($category['name']); ?></strong></td>
                                <td>
                                    <code style="background: #f5f5f5; padding: 2px 6px; border-radius: 3px;">
                                        <?php echo escape($category['slug']); ?>
                                    </code>
                                </td>
                                <td>
                                    <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                        <?php echo $category['car_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($category['car_count'] == 0): ?>
                                            <a href="?action=delete&id=<?php echo $category['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirmDelete('Вы уверены, что хотите удалить эту категорию?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="btn btn-danger btn-sm" style="opacity: 0.5; cursor: not-allowed;" title="Нельзя удалить - есть автомобили">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                        <?php endif; ?>
                                        <a href="../category.php?category=<?php echo $category['slug']; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
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
            <?php echo $action === 'add' ? 'Добавить категорию' : 'Редактировать категорию'; ?>
        </h3>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo escape($category['name'] ?? ''); ?>" 
                       onkeyup="generateSlug(this.value, 'slug')" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (URL) *</label>
                <input type="text" id="slug" name="slug" class="form-control" 
                       value="<?php echo escape($category['slug'] ?? ''); ?>" required>
                <small style="color: #666;">URL-совместимое имя категории (только латинские буквы, цифры и дефисы)</small>
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