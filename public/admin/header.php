<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? escape($title) : 'Админ-панель'; ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: #ffffff;
            border-right: 1px solid #e9ecef;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin: 0;
            padding: 25px 20px;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .sidebar ul {
            list-style: none;
            padding: 10px 0;
            margin: 0;
        }

        .sidebar li {
            margin: 0;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .sidebar a:hover {
            background-color: #f8f9fa;
            color: #2c3e50;
            border-left-color: #3498db;
        }

        .sidebar a.active {
            background-color: #e3f2fd;
            color: #1976d2;
            border-left-color: #1976d2;
        }



        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .header {
            background: white;
            padding: 20px 0;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-weight: 400;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-control-file {
            padding: 5px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-card h3 {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 8px 0;
        }

        .stat-card p {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .image-preview {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 8px;
            background: #f9f9f9;
            border: 1px solid #e1e5e9;
            padding: 4px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination a:hover,
        .pagination a.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .admin-container {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Админ-панель</h2>
            <ul>
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    Дашборд
                </a></li>
                <li><a href="cars.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : ''; ?>">
                    Автомобили
                </a></li>
                <li><a href="manufacturers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manufacturers.php' ? 'active' : ''; ?>">
                    Производители
                </a></li>
                <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    Категории
                </a></li>
                <li><a href="body_types.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'body_types.php' ? 'active' : ''; ?>">
                    Типы кузова
                </a></li>
                <li><a href="engine_types.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'engine_types.php' ? 'active' : ''; ?>">
                    Типы двигателей
                </a></li>
                <li><a href="../index.php" target="_blank">
                    Просмотреть сайт
                </a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1><?php echo isset($title) ? escape($title) : 'Админ-панель'; ?></h1>
                <a href="logout.php" class="logout-btn">
                    Выйти
                </a>
            </div>