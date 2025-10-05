<?php

/**
 * Утилиты для работы с данными
 */
class Utils {
    
    /**
     * Безопасное экранирование HTML
     */
    public static function escape(string $data): string {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Создание slug из строки
     */
    public static function createSlug(string $string): string {
        // Транслитерация русских букв
        $transliteration = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];
        
        $string = mb_strtolower($string, 'UTF-8');
        $string = strtr($string, $transliteration);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
    
    /**
     * Валидация email
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Форматирование даты
     */
    public static function formatDate(string $date, string $format = 'd.m.Y H:i'): string {
        return date($format, strtotime($date));
    }
    
    /**
     * Форматирование числа
     */
    public static function formatNumber(float $number, int $decimals = 0): string {
        return number_format($number, $decimals, '.', ' ');
    }
    
    /**
     * Валидация изображения
     */
    public static function validateImage(array $file): array {
        $errors = [];
        
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Ошибка загрузки файла';
            return $errors;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Недопустимый тип файла. Разрешены: JPEG, PNG, GIF, WebP';
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            $errors[] = 'Размер файла не должен превышать 5 МБ';
        }
        
        return $errors;
    }
    
    /**
     * Загрузка изображения
     */
    public static function uploadImage(array $file, string $targetDir = 'assets/images/'): ?string {
        $errors = self::validateImage($file);
        if (!empty($errors)) {
            return null;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $extension;
        $targetPath = $targetDir . $fileName;
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/' . $targetPath;
        }
        
        return null;
    }
    
    /**
     * Санитизация строки
     */
    public static function sanitizeString(string $string): string {
        return trim(strip_tags($string));
    }
    
    /**
     * Валидация числа в диапазоне
     */
    public static function validateNumber($value, int $min = null, int $max = null): bool {
        if (!is_numeric($value)) {
            return false;
        }
        
        $value = (int)$value;
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Редирект
     */
    public static function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
    
    /**
     * Получение текущего URL
     */
    public static function getCurrentUrl(): string {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
               . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Пагинация
     */
    public static function paginate(int $total, int $perPage = 20, int $currentPage = 1): array {
        $totalPages = ceil($total / $perPage);
        $currentPage = max(1, min($totalPages, $currentPage));
        $offset = ($currentPage - 1) * $perPage;
        
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages
        ];
    }
}