<?php

/**
 * Класс для работы с авторизацией
 */
class Auth {
    
    /**
     * Проверка авторизации
     */
    public static function check(): bool {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
    
    /**
     * Авторизация пользователя
     */
    public static function login(string $username, string $password): bool {
        // В реальном проекте здесь должна быть проверка с БД и хеширование
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            self::generateCSRFToken();
            return true;
        }
        
        return false;
    }
    
    /**
     * Выход из системы
     */
    public static function logout(): void {
        session_destroy();
    }
    
    /**
     * Принудительная проверка авторизации (редирект если не авторизован)
     */
    public static function requireAuth(): void {
        if (!self::check()) {
            Utils::redirect('/admin/login.php');
        }
    }
    
    /**
     * Генерация CSRF токена
     */
    public static function generateCSRFToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Проверка CSRF токена
     */
    public static function verifyCSRFToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Получить текущего пользователя
     */
    public static function user(): ?string {
        return $_SESSION['admin_user'] ?? null;
    }
}