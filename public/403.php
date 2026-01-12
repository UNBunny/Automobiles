<?php
require_once 'bootstrap.php';

$pageTitle = "Доступ запрещен - 403";
$pageDescription = "Доступ к запрашиваемой странице запрещен. У вас нет необходимых прав для просмотра данного ресурса.";
require_once 'templates/header.php';
?>

<div class="error-page">
    <div class="error-code">403</div>
    <h1 class="error-title">Доступ запрещен</h1>
    <p class="error-description">
        К сожалению, у вас нет прав доступа к запрашиваемому ресурсу.
        Эта страница доступна только авторизованным пользователям или администраторам.
    </p>
    <div class="error-actions">
        <a href="/" class="error-button">На главную</a>
        <a href="/cars.php" class="error-button error-button-secondary">Все автомобили</a>
        <a href="/admin/login.php" class="error-button error-button-secondary">Войти в систему</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
