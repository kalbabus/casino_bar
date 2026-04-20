<?php
// header.php - БЕЗ РАЗВЛЕЧЕНИЙ
if (!function_exists('isGuest')) {
    require_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Элитное Казино - Система управления</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="main-nav">
            <div class="nav-logo">
                <h1><i class="fas fa-gem"></i> Элитное Казино</h1>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Главная</a>
                <?php if (!isGuest()): ?>
                    <a href="employees.php" class="nav-link"><i class="fas fa-users"></i> Сотрудники</a>
                    <a href="guests.php" class="nav-link"><i class="fas fa-user-tie"></i> Гости</a>
                    <a href="menu_items.php" class="nav-link"><i class="fas fa-utensils"></i> Меню</a>
                    <a href="orders.php" class="nav-link"><i class="fas fa-credit-card"></i> Заказы</a>
                    <a href="shifts.php" class="nav-link"><i class="fas fa-clock"></i> Смены</a>
                <?php endif; ?>
                <a href="menu_guest.php" class="nav-link"><i class="fas fa-pizza-slice"></i> Заказать</a>
                <a href="guests.php" class="nav-link"><i class="fas fa-users"></i> Гости (просмотр)</a>
            </div>
            
            <div class="user-info">
                <?php if (!isGuest()): ?>
                    <span class="user-name">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo escape($_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Пользователь'); ?>
                        <?php if (isAdmin()): ?>
                            <span style="color: var(--accent);">(Admin)</span>
                        <?php endif; ?>
                    </span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Войти</a>
                <?php endif; ?>
            </div>
        </nav>