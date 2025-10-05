<?php

/**
 * Модель для работы с автомобилями
 */
class CarModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить автомобиль по ID
     */
    public function getById(int $id): ?array {
        $sql = "
            SELECT c.*, m.name as manufacturer_name, m.logo_url as manufacturer_logo,
                   b.name as body_type_name, e.name as engine_type_name
            FROM cars c
            LEFT JOIN manufacturers m ON c.manufacturer_id = m.id
            LEFT JOIN body_types b ON c.body_type_id = b.id
            LEFT JOIN engine_types e ON c.engine_type_id = e.id
            WHERE c.id = ?
        ";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Получить все автомобили с фильтрами
     */
    public function getAll(array $filters = [], string $sort = 'newest', int $limit = null, int $offset = 0): array {
        $sql = "
            SELECT c.*, m.name as manufacturer_name, m.logo_url as manufacturer_logo
            FROM cars c
            JOIN manufacturers m ON c.manufacturer_id = m.id
        ";
        
        $joins = [];
        $where = [];
        $params = [];
        
        // Фильтр по категории
        if (!empty($filters['category'])) {
            $joins[] = "JOIN car_categories cc ON c.id = cc.car_id";
            $joins[] = "JOIN categories cat ON cc.category_id = cat.id";
            $where[] = "cat.slug = ?";
            $params[] = $filters['category'];
        }
        
        // Фильтр по производителю
        if (!empty($filters['manufacturer'])) {
            $where[] = "m.slug = ?";
            $params[] = $filters['manufacturer'];
        }
        
        // Фильтр по году
        if (!empty($filters['year_from'])) {
            $where[] = "c.year >= ?";
            $params[] = (int)$filters['year_from'];
        }
        
        if (!empty($filters['year_to'])) {
            $where[] = "c.year <= ?";
            $params[] = (int)$filters['year_to'];
        }
        
        // Поиск
        if (!empty($filters['search'])) {
            $where[] = "(c.model ILIKE ? OR m.name ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Собираем запрос
        if (!empty($joins)) {
            $sql .= ' ' . implode(' ', $joins);
        }
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        // Сортировка
        $sql .= $this->getSortClause($sort);
        
        // Лимит и смещение
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Подсчет автомобилей с фильтрами
     */
    public function count(array $filters = []): int {
        $sql = "SELECT COUNT(DISTINCT c.id) FROM cars c JOIN manufacturers m ON c.manufacturer_id = m.id";
        
        $joins = [];
        $where = [];
        $params = [];
        
        if (!empty($filters['category'])) {
            $joins[] = "JOIN car_categories cc ON c.id = cc.car_id";
            $joins[] = "JOIN categories cat ON cc.category_id = cat.id";
            $where[] = "cat.slug = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['manufacturer'])) {
            $where[] = "m.slug = ?";
            $params[] = $filters['manufacturer'];
        }
        
        if (!empty($filters['year_from'])) {
            $where[] = "c.year >= ?";
            $params[] = (int)$filters['year_from'];
        }
        
        if (!empty($filters['year_to'])) {
            $where[] = "c.year <= ?";
            $params[] = (int)$filters['year_to'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(c.model ILIKE ? OR m.name ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($joins)) {
            $sql .= ' ' . implode(' ', $joins);
        }
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Создать автомобиль
     */
    public function create(array $data): int {
        $sql = "
            INSERT INTO cars (
                manufacturer_id, model, year, body_type_id, engine_type_id,
                power_hp, battery_capacity_kwh, range_km, acceleration_0_100,
                top_speed_kmh, price, main_image_url, description, slug
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $params = [
            $data['manufacturer_id'],
            $data['model'],
            $data['year'],
            $data['body_type_id'] ?? null,
            $data['engine_type_id'] ?? null,
            $data['power_hp'] ?? null,
            $data['battery_capacity_kwh'] ?? null,
            $data['range_km'] ?? null,
            $data['acceleration_0_100'] ?? null,
            $data['top_speed_kmh'] ?? null,
            $data['price'] ?? null,
            $data['main_image_url'] ?? '',
            $data['description'] ?? '',
            $data['slug']
        ];
        
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Обновить автомобиль
     */
    public function update(int $id, array $data): bool {
        $setParts = [];
        $params = [];
        
        $allowedFields = [
            'manufacturer_id', 'model', 'year', 'body_type_id', 'engine_type_id',
            'power_hp', 'battery_capacity_kwh', 'range_km', 'acceleration_0_100',
            'top_speed_kmh', 'price', 'main_image_url', 'description', 'slug'
        ];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $setParts[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;
        
        $sql = "UPDATE cars SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Удалить автомобиль
     */
    public function delete(int $id): bool {
        $this->db->beginTransaction();
        
        try {
            // Удаляем связи
            $this->db->execute("DELETE FROM car_categories WHERE car_id = ?", [$id]);
            $this->db->execute("DELETE FROM car_features WHERE car_id = ?", [$id]);
            $this->db->execute("DELETE FROM car_images WHERE car_id = ?", [$id]);
            
            // Удаляем автомобиль
            $result = $this->db->execute("DELETE FROM cars WHERE id = ?", [$id]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Увеличить счетчик просмотров
     */
    public function incrementViews(int $id): bool {
        return $this->db->execute("UPDATE cars SET views = views + 1 WHERE id = ?", [$id]);
    }
    
    /**
     * Получить категории автомобиля
     */
    public function getCategories(int $carId): array {
        $sql = "
            SELECT c.* FROM categories c
            JOIN car_categories cc ON c.id = cc.category_id
            WHERE cc.car_id = ?
        ";
        
        return $this->db->fetchAll($sql, [$carId]);
    }
    
    /**
     * Установить категории автомобиля
     */
    public function setCategories(int $carId, array $categoryIds): bool {
        $this->db->beginTransaction();
        
        try {
            // Удаляем старые связи
            $this->db->execute("DELETE FROM car_categories WHERE car_id = ?", [$carId]);
            
            // Добавляем новые
            foreach ($categoryIds as $categoryId) {
                $this->db->execute(
                    "INSERT INTO car_categories (car_id, category_id) VALUES (?, ?)",
                    [$carId, $categoryId]
                );
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Получить популярные автомобили
     */
    public function getPopular(int $limit = 5): array {
        $sql = "
            SELECT c.*, m.name as manufacturer_name
            FROM cars c
            JOIN manufacturers m ON c.manufacturer_id = m.id
            ORDER BY c.views DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Получить недавние автомобили
     */
    public function getRecent(int $limit = 5): array {
        $sql = "
            SELECT c.*, m.name as manufacturer_name
            FROM cars c
            JOIN manufacturers m ON c.manufacturer_id = m.id
            ORDER BY c.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Получение ORDER BY части запроса
     */
    private function getSortClause(string $sort): string {
        switch ($sort) {
            case 'price_asc':
                return " ORDER BY c.price ASC NULLS LAST";
            case 'price_desc':
                return " ORDER BY c.price DESC NULLS LAST";
            case 'popularity':
                return " ORDER BY c.views DESC";
            case 'year_asc':
                return " ORDER BY c.year ASC";
            case 'newest':
            default:
                return " ORDER BY c.year DESC";
        }
    }
}