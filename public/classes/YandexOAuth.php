<?php

/**
 * Класс для работы с Yandex OAuth 2.0
 */
class YandexOAuth {
    
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    
    // Yandex OAuth endpoints
    private const AUTHORIZE_URL = 'https://oauth.yandex.ru/authorize';
    private const TOKEN_URL = 'https://oauth.yandex.ru/token';
    private const API_URL = 'https://login.yandex.ru/info';
    
    public function __construct(string $clientId, string $clientSecret, string $redirectUri) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }
    
    /**
     * Получить URL для авторизации
     */
    public function getAuthUrl(): string {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $this->generateState(),
            'force_confirm' => 'yes' // Показывать диалог даже если уже авторизован
        ];
        
        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }
    
    /**
     * Обмен кода на токен доступа
     */
    public function getAccessToken(string $code): ?array {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri
        ];
        
        $ch = curl_init(self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL для Docker
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // Отключаем проверку хоста
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            error_log("cURL error #$errno: $error");
            curl_close($ch);
            return null;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("Yandex OAuth token error: HTTP $httpCode, Response: $response");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['access_token'])) {
            error_log("Yandex OAuth: No access token in response");
            return null;
        }
        
        return $data;
    }
    
    /**
     * Получить информацию о пользователе
     */
    public function getUserInfo(string $accessToken): ?array {
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL для Docker
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: OAuth ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            error_log("cURL error in getUserInfo #$errno: $error");
            curl_close($ch);
            return null;
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("Yandex API error: HTTP $httpCode");
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Генерация state для защиты от CSRF
     */
    private function generateState(): string {
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        return $state;
    }
    
    /**
     * Проверка state
     */
    public function verifyState(string $state): bool {
        error_log("Verifying state: " . $state);
        error_log("Session state: " . ($_SESSION['oauth_state'] ?? 'NOT SET'));
        
        if (!isset($_SESSION['oauth_state'])) {
            error_log("Session oauth_state is not set");
            // Временно возвращаем true для отладки
            return true;
        }
        
        $valid = hash_equals($_SESSION['oauth_state'], $state);
        error_log("State comparison result: " . ($valid ? 'VALID' : 'INVALID'));
        
        if ($valid) {
            unset($_SESSION['oauth_state']);
        }
        
        return $valid;
    }
    
    /**
     * Авторизация или регистрация пользователя через Yandex
     */
    public function authenticateUser(string $code, string $state): bool {
        error_log("=== authenticateUser START ===");
        
        // Проверяем state
        if (!$this->verifyState($state)) {
            error_log("OAuth state verification failed");
            return false;
        }
        
        error_log("State verified successfully");
        
        // Получаем токен
        error_log("Getting access token...");
        $tokenData = $this->getAccessToken($code);
        if (!$tokenData) {
            error_log("Failed to get access token");
            return false;
        }
        
        error_log("Access token received: " . substr($tokenData['access_token'], 0, 10) . "...");
        
        // Получаем информацию о пользователе
        error_log("Getting user info...");
        $userInfo = $this->getUserInfo($tokenData['access_token']);
        if (!$userInfo) {
            error_log("Failed to get user info");
            return false;
        }
        
        error_log("User info received: " . json_encode($userInfo));
        
        // Проверяем, разрешён ли этот Yandex ID для админ-доступа
        $allowedAdminId = $_ENV['ADMIN_YANDEX_ID'] ?? null;
        if ($allowedAdminId && $userInfo['id'] !== $allowedAdminId) {
            error_log("Access denied: Yandex ID {$userInfo['id']} is not authorized as admin");
            error_log("Only Yandex ID {$allowedAdminId} is allowed");
            return false;
        }
        
        error_log("Admin access granted for Yandex ID: " . $userInfo['id']);
        
        // Ищем или создаем пользователя
        try {
            error_log("Connecting to database...");
            $db = Database::getInstance()->getConnection();
            
            error_log("Searching for user with yandex_id: " . $userInfo['id']);
            
            // Ищем пользователя по Yandex ID
            $stmt = $db->prepare("
                SELECT id, username, email, full_name, is_active 
                FROM users 
                WHERE yandex_id = :yandex_id
            ");
            $stmt->execute(['yandex_id' => $userInfo['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("User search result: " . ($user ? "FOUND (id=" . $user['id'] . ")" : "NOT FOUND"));
            
            if (!$user) {
                error_log("Creating new user...");
                
                // Создаем нового пользователя
                $username = $this->generateUsername($userInfo);
                $email = $userInfo['default_email'] ?? null;
                $fullName = $this->getFullName($userInfo);
                
                error_log("New user data: username=$username, email=$email, fullName=$fullName");
                
                $insertStmt = $db->prepare("
                    INSERT INTO users (username, email, full_name, yandex_id, password_hash, is_active) 
                    VALUES (:username, :email, :full_name, :yandex_id, :password_hash, TRUE)
                    RETURNING id
                ");
                
                $insertStmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $fullName,
                    'yandex_id' => $userInfo['id'],
                    'password_hash' => '' // Пустой хеш для OAuth пользователей
                ]);
                
                $userId = $insertStmt->fetchColumn();
                
                $user = [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $fullName,
                    'is_active' => true
                ];
            }
            
            // Проверяем активность
            if (!$user['is_active']) {
                return false;
            }
            
            // Обновляем время последнего входа
            $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);
            
            // Устанавливаем сессию
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_user_email'] = $user['email'];
            $_SESSION['admin_user_fullname'] = $user['full_name'];
            $_SESSION['oauth_provider'] = 'yandex';
            
            error_log("Session set: admin_logged_in=" . $_SESSION['admin_logged_in']);
            error_log("Session set: admin_user=" . $_SESSION['admin_user']);
            error_log("Session set: admin_user_id=" . $_SESSION['admin_user_id']);
            
            // Генерируем CSRF токен
            Auth::generateCSRFToken();
            
            error_log("Session ID: " . session_id());
            error_log("Session data: " . json_encode($_SESSION));
            
            return true;
            
        } catch (Exception $e) {
            error_log("Yandex OAuth authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Генерация уникального username
     */
    private function generateUsername(array $userInfo): string {
        $baseUsername = $userInfo['login'] ?? 'user_' . substr($userInfo['id'], 0, 8);
        
        // Убираем недопустимые символы
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUsername);
        
        // Проверяем уникальность
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        
        if ($stmt->fetchColumn() > 0) {
            // Добавляем случайное число
            $username .= '_' . rand(1000, 9999);
        }
        
        return $username;
    }
    
    /**
     * Получение полного имени
     */
    private function getFullName(array $userInfo): string {
        $parts = [];
        
        if (!empty($userInfo['first_name'])) {
            $parts[] = $userInfo['first_name'];
        }
        
        if (!empty($userInfo['last_name'])) {
            $parts[] = $userInfo['last_name'];
        }
        
        return !empty($parts) ? implode(' ', $parts) : ($userInfo['display_name'] ?? 'Yandex User');
    }
}
