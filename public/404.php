<?php
require_once 'bootstrap.php';

$pageTitle = "Страница не найдена - 404";
require_once 'templates/header.php';
?>

<div class="error-page">
    <div class="error-code">404</div>
    <h1 class="error-title">Страница не найдена</h1>
    <p class="error-description">
        К сожалению, запрашиваемая вами страница не существует или была удалена.
        Попробуйте вернуться на главную страницу или воспользуйтесь поиском.
    </p>
    <div class="error-actions">
        <a href="/" class="error-button">На главную</a>
        <a href="/cars.php" class="error-button error-button-secondary">Все автомобили</a>
        <a href="/manufacturers.php" class="error-button error-button-secondary">Производители</a>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
