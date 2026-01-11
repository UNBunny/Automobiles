<?php
/**
 * Скрипт для создания администратора
 * ВАЖНО: Удалите этот файл после создания администратора!
 */

require_once '../bootstrap.php';

// Отключаем проверку авторизации для этого скрипта
// Auth::requireAuth();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Utils::sanitizeString($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = Utils::sanitizeString($_POST['email'] ?? '');
    $fullName = Utils::sanitizeString($_POST['full_name'] ?? '');
    
    // Валидация
    $errors = [];
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Логин должен содержать минимум 3 символа';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Проверяем силу пароля
    $passwordCheck = Auth::validatePasswordStrength($password);
    if (!$passwordCheck['valid']) {
        $errors = array_merge($errors, $passwordCheck['errors']);
    }
    
    if (empty($errors)) {
        if (Auth::createUser($username, $password, $email, $fullName)) {
            $message = 'Пользователь успешно создан! Теперь вы можете войти в систему.';
            $messageType = 'success';
        } else {
            $message = 'Ошибка при создании пользователя. Возможно, такой логин или email уже существует.';
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание администратора</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 500px;
            max-width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header i {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .header h2 {
            color: #333;
            font-weight: 300;
            font-size: 28px;
        }

        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeeba;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            padding-left: 15px;
        }

        .password-requirements li {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-user-plus"></i>
            <h2>Создание администратора</h2>
        </div>

        <div class="warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Внимание!</strong> Удалите этот файл после создания администратора!
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Логин *</label>
                <input type="text" id="username" name="username" required minlength="3">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="full_name">Полное имя</label>
                <input type="text" id="full_name" name="full_name">
            </div>

            <div class="form-group">
                <label for="password">Пароль *</label>
                <input type="password" id="password" name="password" required>
                <ul class="password-requirements">
                    <li>Минимум 8 символов</li>
                    <li>Минимум одна заглавная буква</li>
                    <li>Минимум одна строчная буква</li>
                    <li>Минимум одна цифра</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="confirm_password">Подтверждение пароля *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-user-check"></i> Создать пользователя
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i> Вернуться на страницу входа
            </a>
        </div>
    </div>
</body>
</html>
