<?php

/**
 * Базовая модель для категорий, типов кузова и двигателей
 */
abstract class BaseReferenceModel {
    protected $db;
    protected $tableName;
    protected $relatedTable;
    protected $relatedField;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить все записи
     */
    public function getAll(string $search = '', int $limit = null, int $offset = 0): array {
        $sql = "
            SELECT t.*, 
                   (SELECT COUNT(*) FROM {$this->relatedTable} WHERE {$this->relatedField} = t.id) as car_count
            FROM {$this->tableName} t
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE (t.name ILIKE ? OR t.slug ILIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY t.name";
        
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Получить по ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Получить по slug
     */
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE slug = ?";
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    /**
     * Подсчет записей
     */
    public function count(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM {$this->tableName}";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE (name ILIKE ? OR slug ILIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Создать запись
     */
    public function create(array $data): int {
        $sql = "INSERT INTO {$this->tableName} (name, slug) VALUES (?, ?)";
        $params = [$data['name'], $data['slug']];
        
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Обновить запись
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->tableName} SET name = ?, slug = ? WHERE id = ?";
        $params = [$data['name'], $data['slug'], $id];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Удалить запись
     */
    public function delete(int $id): bool {
        // Проверяем использование
        $carCount = $this->getCarCount($id);
        if ($carCount > 0) {
            throw new Exception("Нельзя удалить запись, которая используется автомобилями ($carCount шт.)");
        }
        
        return $this->db->execute("DELETE FROM {$this->tableName} WHERE id = ?", [$id]);
    }
    
    /**
     * Получить количество связанных автомобилей
     */
    public function getCarCount(int $id): int {
        $sql = "SELECT COUNT(*) FROM {$this->relatedTable} WHERE {$this->relatedField} = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        return (int)$result['count'];
    }
    
    /**
     * Проверить уникальность slug
     */
    public function isSlugUnique(string $slug, int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'] === 0;
    }
}

/**
 * Модель для категорий
 */
class CategoryModel extends BaseReferenceModel {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'categories';
        $this->relatedTable = 'car_categories';
        $this->relatedField = 'category_id';
    }
    
    /**
     * Переопределяем метод подсчета для категорий (особая связь many-to-many)
     */
    public function getCarCount(int $id): int {
        $sql = "SELECT COUNT(*) FROM car_categories WHERE category_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        return (int)$result['count'];
    }
}

/**
 * Модель для типов кузова
 */
class BodyTypeModel extends BaseReferenceModel {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'body_types';
        $this->relatedTable = 'cars';
        $this->relatedField = 'body_type_id';
    }
}

/**
 * Модель для типов двигателей
 */
class EngineTypeModel extends BaseReferenceModel {
    public function __construct() {
        parent::__construct();
        $this->tableName = 'engine_types';
        $this->relatedTable = 'cars';
        $this->relatedField = 'engine_type_id';
    }
}