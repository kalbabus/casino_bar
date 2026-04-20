<?php
// header.php - ИСПРАВЛЕННАЯ ВЕРСИЯ
// НИКАКОГО HTML до PHP кода!

// Функции для проверки авторизации должны быть определены ДО вывода
if (!function_exists('isGuest')) {
    function isGuest() {
        return !isset($_SESSION['user_id']) || $_SESSION['role'] == 'guest';
    }
    
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }
    
    function getUserRole() {
        return $_SESSION['role'] ?? 'guest';
    }
    
    function getUserName() {
        return $_SESSION['first_name'] ?? 'Гость';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Элитное Казино - Система управления</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Основные стили -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root {
            --primary: #8B0000;
            --secondary: #C71585;
            --accent: #D4AF37;
            --dark: #1A1A1A;
            --light: #F5F5F5;
            --gray: #333333;
            --secondary-light: #888;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
            color: var(--light);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        h1, h2, h3, h4 {
            font-family: 'Cinzel', serif;
            color: var(--accent);
        }
        
        a {
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        a:hover {
            color: var(--accent);
        }
        
        .card {
            background: rgba(26, 26, 26, 0.8);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            padding-bottom: 15px;
        }
        
        .card-header h3 {
            margin: 0;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(199, 21, 133, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--accent), #B8860B);
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #B8860B, var(--accent));
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .data-table th {
            color: var(--accent);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .data-table tr:hover {
            background: rgba(212, 175, 55, 0.05);
        }
        
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-active {
            background: rgba(0, 128, 0, 0.2);
            color: #90EE90;
        }
        
        .status-inactive {
            background: rgba(255, 0, 0, 0.2);
            color: #FF7F7F;
        }
        
        .status-pending {
            background: rgba(255, 165, 0, 0.2);
            color: #FFD700;
        }
        
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            color: white;
            margin: 0 5px;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: rgba(0, 123, 255, 0.2);
            color: #7CB9E8;
        }
        
        .btn-edit:hover {
            background: rgba(0, 123, 255, 0.4);
        }
        
        .btn-delete {
            background: rgba(220, 53, 69, 0.2);
            color: #FF7F7F;
        }
        
        .btn-delete:hover {
            background: rgba(220, 53, 69, 0.4);
        }
        
        .btn-view {
            background: rgba(40, 167, 69, 0.2);
            color: #90EE90;
        }
        
        .btn-view:hover {
            background: rgba(40, 167, 69, 0.4);
        }
        
        .amount-cell {
            color: var(--accent);
            font-weight: bold;
        }
        
        .vip-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .vip-diamond {
            background: linear-gradient(45deg, #B9F2FF, #E0FFFF);
            color: #1E90FF;
        }
        
        .vip-gold {
            background: linear-gradient(45deg, #FFD700, #F0E68C);
            color: #8B7500;
        }
        
        .vip-silver {
            background: linear-gradient(45deg, #C0C0C0, #DCDCDC);
            color: #696969;
        }
        
        .no-data {
            text-align: center;
            padding: 50px 20px;
        }
        
        .no-data i {
            margin-bottom: 20px;
        }
        
        .no-data p {
            color: var(--secondary-light);
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #FF7F7F;
        }
        
        /* Навигация */
        .main-nav {
            background: rgba(26, 26, 26, 0.9);
            border-radius: 10px;
            padding: 15px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        
        .nav-logo h1 {
            color: var(--accent);
            font-size: 1.8rem;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: var(--light);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: rgba(212, 175, 55, 0.2);
            color: var(--accent);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-name {
            color: var(--accent);
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(220, 53, 69, 0.2);
            color: #FF7F7F;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Навигация -->
        <nav class="main-nav">
            <div class="nav-logo">
                <h1><i class="fas fa-gem"></i> Элитное Казино</h1>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Главная</a>
                <?php if (!isGuest()): ?>
                    <a href="employees.php" class="nav-link"><i class="fas fa-users"></i> Сотрудники</a>
                    <a href="guests.php" class="nav-link"><i class="fas fa-user-tie"></i> Гости</a>
                    <a href="menu_items.php" class="nav-link"><i class="fas fa-champagne-glasses"></i> Меню</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-credit-card"></i> Заказы</a>
                    <a href="order_details.php" class="nav-link"><i class="fas fa-list-alt"></i> Детали заказов</a>
                    <a href="shifts.php" class="nav-link"><i class="fas fa-clock"></i> Смены</a>
                <?php else: ?>
                    <a href="guests.php" class="nav-link"><i class="fas fa-user-tie"></i> Гости</a>
                <?php endif; ?>
            </div>
            
            <div class="user-info">
                <?php if (!isGuest()): ?>
                    <span class="user-name"><?php echo $_SESSION['first_name']; ?></span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Войти</a>
                <?php endif; ?>
            </div>
        </nav>