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
        try {
            $db = Database::getInstance()->getConnection();
            
            // Получаем пользователя из БД
            $stmt = $db->prepare("
                SELECT id, username, password_hash, email, full_name, is_active, last_login
                FROM users 
                WHERE username = :username AND is_active = TRUE
            ");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Проверяем существование пользователя и пароль
            if (!$user) {
                return false;
            }
            
            // Если пользователь зарегистрирован через OAuth и у него нет пароля
            if (empty($user['password_hash'])) {
                return false;
            }
            
            // Проверяем пароль
            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }
            
            // Обновляем время последнего входа
            $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);
            
            // Устанавливаем данные сессии
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_user_email'] = $user['email'];
            $_SESSION['admin_user_fullname'] = $user['full_name'];
            
            // Генерируем CSRF токен
            self::generateCSRFToken();
            
            // Регенерируем ID сессии для безопасности
            session_regenerate_id(true);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Выход из системы
     */
    public static function logout(): void {
        $_SESSION = [];
        
        // Удаляем cookie сессии
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
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
    
    /**
     * Получить ID текущего пользователя
     */
    public static function userId(): ?int {
        return $_SESSION['admin_user_id'] ?? null;
    }
    
    /**
     * Получить полное имя текущего пользователя
     */
    public static function userFullName(): ?string {
        return $_SESSION['admin_user_fullname'] ?? null;
    }
    
    /**
     * Проверка, является ли пользователь OAuth пользователем
     */
    public static function isOAuthUser(): bool {
        return isset($_SESSION['oauth_provider']);
    }
    
    /**
     * Получить провайдер OAuth
     */
    public static function getOAuthProvider(): ?string {
        return $_SESSION['oauth_provider'] ?? null;
    }
    
    /**
     * Создание нового пользователя
     */
    public static function createUser(string $username, string $password, string $email, string $fullName = ''): bool {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Хешируем пароль с оптимальными настройками безопасности
            // PASSWORD_BCRYPT с cost=12 обеспечивает хороший баланс безопасности и производительности
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $db->prepare("
                INSERT INTO users (username, password_hash, email, full_name) 
                VALUES (:username, :password_hash, :email, :full_name)
            ");
            
            return $stmt->execute([
                'username' => $username,
                'password_hash' => $passwordHash,
                'email' => $email,
                'full_name' => $fullName
            ]);
            
        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Смена пароля
     */
    public static function changePassword(int $userId, string $oldPassword, string $newPassword): bool {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Проверяем старый пароль
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
                return false;
            }
            
            // Обновляем пароль с оптимальными настройками безопасности
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $updateStmt = $db->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
            
            return $updateStmt->execute([
                'password_hash' => $newHash,
                'id' => $userId
            ]);
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверка силы пароля
     */
    public static function validatePasswordStrength(string $password): array {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать минимум 8 символов';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну цифру';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}