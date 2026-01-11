<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? escape($title) : 'Electric Cars'; ?></title>
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
        <div class="container flex justify-between items-center py-3">
            <!-- Логотип -->
            <div class="logo">
                <a href="/"><img src="<?php echo IMAGES_PATH; ?>/logo.png" alt="Logo" class="h-8"></a>
            </div>
    
            <!-- Поиск -->
            <div class="search-container">
                <form action="/cars.php" method="get">
                    <input type="text" name="search" class="search-input" placeholder="Поиск автомобилей..." value="<?php echo isset($_GET['search']) ? Utils::escape($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-button">
                        <span class="material-icons">search</span>
                    </button>
                </form>
            </div>

            <!-- Навигация -->
            <nav class="nav" id="nav">
                <ul class="flex space-x-6">
                    <li><a href="/" class="text-gray-700 hover:text-blue-500">Главная</a></li>
                    <li><a href="/cars.php" class="text-gray-700 hover:text-blue-500">Автомобили</a></li>
                    <li><a href="/manufacturers.php" class="text-gray-700 hover:text-blue-500">Производители</a></li>
                    <li><a href="/faq.php" class="text-gray-700 hover:text-blue-500">FAQ</a></li>
                </ul>
            </nav>
    
            <!-- Кнопки справа -->
            <div class="header-actions flex items-center">
                <button class="burger-menu" id="burger-menu">
                    <span class="material-icons">menu</span>
                </button>
                <a href="/admin" class="view-button">Админ</a>
            </div>
        </div>
    </header>

    <div class="container py-8">