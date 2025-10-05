<?php

/**
 * Модель для работы с производителями
 */
class ManufacturerModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить всех производителей
     */
    public function getAll(string $search = '', int $limit = null, int $offset = 0): array {
        $sql = "
            SELECT m.*, 
                   (SELECT COUNT(*) FROM cars WHERE manufacturer_id = m.id) as car_count
            FROM manufacturers m
        ";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE (m.name ILIKE ? OR m.country ILIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY m.name";
        
        if ($limit !== null) {
            $sql .= " LIMIT $limit";
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Получить производителя по ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM manufacturers WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Получить производителя по slug
     */
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT * FROM manufacturers WHERE slug = ?";
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    /**
     * Подсчет производителей
     */
    public function count(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM manufacturers";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE (name ILIKE ? OR country ILIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Создать производителя
     */
    public function create(array $data): int {
        $sql = "
            INSERT INTO manufacturers (name, logo_url, description, founded_year, country, slug)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $params = [
            $data['name'],
            $data['logo_url'] ?? '',
            $data['description'] ?? '',
            $data['founded_year'] ?? null,
            $data['country'] ?? '',
            $data['slug']
        ];
        
        $this->db->execute($sql, $params);
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Обновить производителя
     */
    public function update(int $id, array $data): bool {
        $setParts = [];
        $params = [];
        
        $allowedFields = ['name', 'logo_url', 'description', 'founded_year', 'country', 'slug'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($setParts)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE manufacturers SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Удалить производителя
     */
    public function delete(int $id): bool {
        // Проверяем, есть ли автомобили
        $carCount = $this->getCarCount($id);
        if ($carCount > 0) {
            throw new Exception("Нельзя удалить производителя, у которого есть автомобили ($carCount шт.)");
        }
        
        return $this->db->execute("DELETE FROM manufacturers WHERE id = ?", [$id]);
    }
    
    /**
     * Получить количество автомобилей производителя
     */
    public function getCarCount(int $manufacturerId): int {
        $sql = "SELECT COUNT(*) FROM cars WHERE manufacturer_id = ?";
        $result = $this->db->fetchOne($sql, [$manufacturerId]);
        return (int)$result['count'];
    }
    
    /**
     * Проверить уникальность slug
     */
    public function isSlugUnique(string $slug, int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM manufacturers WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'] === 0;
    }
}