<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Title -->
    <title><?php echo isset($pageTitle) ? Utils::escape($pageTitle) . ' - Automobiles' : 'Automobiles - Каталог современных автомобилей'; ?></title>
    
    <!-- Meta Description -->
    <meta name="description" content="<?php echo isset($pageDescription) ? Utils::escape($pageDescription) : 'Каталог современных автомобилей с подробными характеристиками. Электромобили, гибриды, бензиновые и дизельные автомобили от ведущих производителей.'; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo ASSETS_PATH; ?>/images/favicon.svg">
    <link rel="alternate icon" href="<?php echo ASSETS_PATH; ?>/images/favicon.svg" type="image/svg+xml">
    
    <!-- Open Graph -->
    <meta property="og:type" content="<?php echo isset($ogType) ? $ogType : 'website'; ?>">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? Utils::escape($pageTitle) : 'Automobiles'; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? Utils::escape($pageDescription) : 'Каталог современных автомобилей с подробными характеристиками'; ?>">
    <meta property="og:url" content="<?php echo 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="Automobiles">
    <?php if (isset($ogImage)): ?>
    <meta property="og:image" content="<?php echo Utils::escape($ogImage); ?>">
    <?php endif; ?>
    
    <!-- Preconnect для оптимизации загрузки шрифтов -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>/css/styles.css">
</head>
<body>
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container header-wrapper">
            <!-- Логотип -->
            <div class="logo">
                <a href="/" title="Automobiles - Главная страница"><img src="<?php echo IMAGES_PATH; ?>/logo.png" alt="Automobiles - Каталог автомобилей" class="h-8"></a>
            </div>
    
            <!-- Поиск -->
            <div class="search-container">
                <form action="/cars.php" method="get" role="search" aria-label="Поиск автомобилей">
                    <input type="text" name="search" class="search-input" placeholder="Поиск автомобилей..." aria-label="Введите название автомобиля или производителя" value="<?php echo isset($_GET['search']) ? Utils::escape($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-button" title="Найти автомобиль" aria-label="Поиск">
                        <span class="material-icons">search</span>
                    </button>
                </form>
            </div>

            <!-- Навигация -->
            <nav class="nav" id="nav" aria-label="Основная навигация">
                <ul class="flex space-x-6">
                    <li><a href="/" class="text-gray-700 hover:text-blue-500" title="Главная страница">Главная</a></li>
                    <li><a href="/cars.php" class="text-gray-700 hover:text-blue-500" title="Каталог всех автомобилей">Автомобили</a></li>
                    <li><a href="/manufacturers.php" class="text-gray-700 hover:text-blue-500" title="Список производителей">Производители</a></li>
                    <li><a href="/faq.php" class="text-gray-700 hover:text-blue-500" title="Часто задаваемые вопросы">FAQ</a></li>
                    <li class="mobile-only"><a href="/admin" class="text-gray-700 hover:text-blue-500" title="Панель администратора">Админ</a></li>
                </ul>
            </nav>
    
            <!-- Кнопки справа -->
            <div class="header-actions flex items-center">
                <a href="/admin" class="view-button desktop-only" title="Вход в панель администратора">Админ</a>
                <button class="burger-menu" id="burger-menu">
                    <span class="material-icons">menu</span>
                </button>
            </div>
        </div>
    </header>

    <div class="container py-8">