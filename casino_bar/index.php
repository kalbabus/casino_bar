<?php
// index.php - исправленная версия (без рулетки)
require_once 'config.php';
require_once 'header.php';

// Проверяем, гость ли пользователь
$is_guest = isGuest();
$user_role = getUserRole();
$user_name = getUserName();

// Получаем статистику с учетом прав доступа
$stats = [];

// Сотрудники (только для админов и сотрудников)
if (!isGuest()) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
        $stats['employees'] = $stmt->fetch()['count'];
    } catch (Exception $e) {
        $stats['employees'] = 0;
    }
} else {
    $stats['employees'] = 0;
}

// Гости (доступно всем)
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests");
    $stats['guests'] = $stmt->fetch()['count'];
} catch (Exception $e) {
    $stats['guests'] = 0;
}

// VIP гости (с большими тратами)
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests WHERE total_spent > 10000");
    $stats['vip_guests'] = $stmt->fetch()['count'];
} catch (Exception $e) {
    $stats['vip_guests'] = 0;
}

// Позиции меню
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1");
    $stats['menu_items'] = $stmt->fetch()['count'];
} catch (Exception $e) {
    $stats['menu_items'] = 0;
}

// Общая выручка (только для авторизованных пользователей)
if (!isGuest()) {
    try {
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
    } catch (Exception $e) {
        $stats['total_revenue'] = 0;
    }
    
    // Выручка за сегодня
    try {
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_time) = ?");
        $stmt->execute([$today]);
        $stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;
    } catch (Exception $e) {
        $stats['today_revenue'] = 0;
    }
    
    // Активные смены
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM shifts WHERE status = 'active'");
        $stats['active_shifts'] = $stmt->fetch()['count'];
    } catch (Exception $e) {
        $stats['active_shifts'] = 0;
    }
} else {
    $stats['total_revenue'] = 0;
    $stats['today_revenue'] = 0;
    $stats['active_shifts'] = 0;
}

// Получаем дату и время для отображения
$current_time = date('H:i');
$current_date = date('d.m.Y');
$greeting = match(true) {
    ($current_time >= '05:00' && $current_time < '12:00') => 'Доброе утро',
    ($current_time >= '12:00' && $current_time < '18:00') => 'Добрый день',
    ($current_time >= '18:00' && $current_time < '23:00') => 'Добрый вечер',
    default => 'Доброй ночи'
};
?>

<!-- Приветственное сообщение -->
<div class="welcome-message">
    <div class="welcome-content">
        <h1 class="typing-effect"><?php echo $greeting; ?>, <?php echo isGuest() ? 'уважаемый гость' : escape($_SESSION['first_name']); ?>!</h1>
        <p class="welcome-subtitle">
            <i class="fas fa-calendar"></i> Сегодня <?php echo $current_date; ?> 
            <span class="separator">•</span> 
            <i class="fas fa-clock"></i> <?php echo $current_time; ?>
            <span class="separator">•</span>
            <?php if (isGuest()): ?>
                <i class="fas fa-user-clock"></i> Гостевой доступ
            <?php elseif (isAdmin()): ?>
                <i class="fas fa-crown"></i> Администратор
            <?php else: ?>
                <i class="fas fa-user-tie"></i> Сотрудник
            <?php endif; ?>
        </p>
    </div>
    <div class="welcome-stats">
        <div class="stat-badge">
            <i class="fas fa-users"></i>
            <span><?php echo $stats['guests']; ?> гостей</span>
        </div>
        <?php if (!isGuest()): ?>
        <div class="stat-badge">
            <i class="fas fa-coins"></i>
            <span><?php echo number_format($stats['total_revenue'], 0, '.', ' '); ?> ₽</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Уведомление для гостей -->
<?php if (isGuest()): ?>
<div class="guest-notice">
    <div class="notice-header">
        <i class="fas fa-info-circle"></i>
        <h3>Гостевой режим</h3>
    </div>
    <p>Вы вошли как демо-гость. Для полного доступа ко всем функциям системы требуется авторизация.</p>
    <div class="notice-actions">
        <a href="login.php" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Войти в систему
        </a>
        <a href="#" class="btn-features" id="showFeatures">
            <i class="fas fa-list"></i> Показать доступные функции
        </a>
    </div>
    
    <div class="guest-features" id="guestFeatures" style="display: none;">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h4>Просмотр статистики</h4>
                <p>Общая информация о заведении</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h4>VIP гости</h4>
                <p>Список лучших посетителей</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h4>Обзорная информация</h4>
                <p>Базовые данные о работе</p>
            </div>
        </div>
        <div class="features-restricted">
            <h5><i class="fas fa-lock"></i> Ограниченный доступ:</h5>
            <ul>
                <li>Управление сотрудниками</li>
                <li>Финансовая статистика</li>
                <li>Управление заказами</li>
                <li>Контроль смен</li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Панель быстрого доступа -->
<div class="quick-access-panel">
    <div class="quick-access-title">
        <h3><i class="fas fa-bolt"></i> Быстрый доступ</h3>
        <?php if (isGuest()): ?>
            <p class="access-note">Некоторые функции недоступны в гостевом режиме</p>
        <?php endif; ?>
    </div>
    <div class="quick-access-grid">
        <?php if (!isGuest()): ?>
        <a href="employees.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="card-content">
                <h4>Сотрудники</h4>
                <p><?php echo $stats['employees']; ?> чел.</p>
                <span class="card-badge">Только для персонала</span>
            </div>
        </a>
        <?php endif; ?>
        
        <a href="guests.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="card-content">
                <h4>VIP Гости</h4>
                <p><?php echo $stats['guests']; ?> чел.</p>
                <span class="card-badge">VIP: <?php echo $stats['vip_guests']; ?></span>
            </div>
        </a>
        
        <?php if (!isGuest()): ?>
        <a href="menu_items.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-champagne-glasses"></i>
            </div>
            <div class="card-content">
                <h4>Меню</h4>
                <p><?php echo $stats['menu_items']; ?> позиций</p>
                <span class="card-badge">Только для персонала</span>
            </div>
        </a>
        
        <a href="orders.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="card-content">
                <h4>Заказы</h4>
                <p><?php echo number_format($stats['total_revenue'], 0, '.', ' '); ?> ₽</p>
                <span class="card-badge">Только для персонала</span>
            </div>
        </a>
        
        <a href="order_details.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="card-content">
                <h4>Детали заказов</h4>
                <p>Подробная информация</p>
                <span class="card-badge">Только для персонала</span>
            </div>
        </a>
        
        <a href="shifts.php" class="quick-access-card">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h4>Смены</h4>
                <p><?php echo $stats['active_shifts']; ?> активных</p>
                <span class="card-badge">Только для персонала</span>
            </div>
        </a>
        <?php else: ?>
        <!-- Альтернативные карточки для гостей -->
        <div class="quick-access-card guest-restricted" title="Требуется авторизация">
            <div class="card-icon">
                <i class="fas fa-champagne-glasses"></i>
            </div>
            <div class="card-content">
                <h4>Меню</h4>
                <p>Доступ ограничен</p>
                <span class="card-badge"><i class="fas fa-lock"></i></span>
            </div>
        </div>
        
        <div class="quick-access-card guest-restricted" title="Требуется авторизация">
            <div class="card-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="card-content">
                <h4>Заказы</h4>
                <p>Доступ ограничен</p>
                <span class="card-badge"><i class="fas fa-lock"></i></span>
            </div>
        </div>
        
        <div class="quick-access-card guest-restricted" title="Требуется авторизация">
            <div class="card-icon">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="card-content">
                <h4>Детали заказов</h4>
                <p>Доступ ограничен</p>
                <span class="card-badge"><i class="fas fa-lock"></i></span>
            </div>
        </div>
        
        <div class="quick-access-card guest-restricted" title="Требуется авторизация">
            <div class="card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
                <h4>Смены</h4>
                <p>Доступ ограничен</p>
                <span class="card-badge"><i class="fas fa-lock"></i></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Основная статистика -->
<div class="dashboard">
    <!-- Статистика -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3><i class="fas fa-crown"></i> Элитные сотрудники</h3>
            <p><?php echo $stats['employees']; ?></p>
            <p style="font-size: 0.9em; color: var(--secondary-light);">Обслуживающий персонал</p>
            <?php if (isGuest()): ?>
                <div class="access-restricted">
                    <i class="fas fa-lock"></i> Требуется авторизация
                </div>
            <?php endif; ?>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-user-tie"></i> VIP Гости</h3>
            <p><?php echo $stats['guests']; ?></p>
            <p style="font-size: 0.9em; color: var(--secondary-light);">
                <?php echo $stats['vip_guests']; ?> особо важных персон
            </p>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-champagne-glasses"></i> Изысканное меню</h3>
            <p><?php echo $stats['menu_items']; ?></p>
            <p style="font-size: 0.9em; color: var(--secondary-light);">Премиальные позиции</p>
            <?php if (isGuest()): ?>
                <div class="access-restricted">
                    <i class="fas fa-lock"></i> Требуется авторизация
                </div>
            <?php endif; ?>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-gem"></i> Общая выручка</h3>
            <?php if (!isGuest()): ?>
                <p><?php echo number_format($stats['total_revenue'], 0, '.', ' '); ?> руб.</p>
                <p style="font-size: 0.9em; color: var(--secondary-light);">
                    Сегодня: <?php echo number_format($stats['today_revenue'], 0, '.', ' '); ?> руб.
                </p>
            <?php else: ?>
                <p style="color: var(--accent); font-size: 1.2em;">Доступно после авторизации</p>
                <p style="font-size: 0.9em; color: var(--secondary-light);">
                    <a href="login.php" style="color: var(--secondary);">Войдите для просмотра</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Блок с активностью -->
    <?php if (!isGuest()): ?>
    <div class="activity-grid">
        <!-- Последние транзакции -->
        <div class="recent-activity">
            <h3><i class="fas fa-history"></i> Последние транзакции</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Сумма</th>
                        <th>Дата</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT o.*, g.first_name, g.last_name 
                                            FROM orders o 
                                            LEFT JOIN guests g ON o.guest_id = g.id 
                                            ORDER BY o.order_time DESC LIMIT 8");
                        while ($row = $stmt->fetch()):
                            $statusClass = strtolower($row['status']);
                    ?>
                    <tr>
                        <td><a href="orders.php?edit=<?php echo $row['id']; ?>" class="order-link">#<?php echo escape($row['id']); ?></a></td>
                        <td>
                            <span class="order-type order-type-<?php echo $row['order_type']; ?>">
                                <?php echo $row['order_type'] == 'game' ? '🎰 Игра' : '🍸 Бар'; ?>
                            </span>
                        </td>
                        <td><strong><?php echo escape($row['total_amount']); ?> руб.</strong></td>
                        <td><?php echo date('H:i', strtotime($row['order_time'])); ?></td>
                        <td>
                            <span class="status status-<?php echo $statusClass; ?>">
                                <?php echo escape($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php } catch (Exception $e) { ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--secondary-light);">
                            <i class="fas fa-exclamation-triangle"></i> Нет данных для отображения
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="view-all">
                <a href="orders.php" class="btn-view-all">
                    <i class="fas fa-eye"></i> Просмотреть все
                </a>
            </div>
        </div>
        
        <!-- Активные смены -->
        <div class="active-shifts">
            <h3><i class="fas fa-clock"></i> Активные смены</h3>
            <?php
            try {
                $stmt = $pdo->query("SELECT s.*, e.first_name, e.last_name 
                                    FROM shifts s 
                                    JOIN employees e ON s.employee_id = e.id 
                                    WHERE s.status = 'active' 
                                    ORDER BY s.start_time DESC 
                                    LIMIT 3");
                
                if ($stmt->rowCount() > 0):
                    while ($row = $stmt->fetch()):
            ?>
            <div class="shift-card">
                <div class="shift-header">
                    <h4><?php echo escape($row['first_name'] . ' ' . $row['last_name']); ?></h4>
                    <span class="shift-status status-active">Активна</span>
                </div>
                <div class="shift-details">
                    <p><i class="fas fa-clock"></i> Начало: <?php echo date('H:i', strtotime($row['start_time'])); ?></p>
                    <p><i class="fas fa-cash-register"></i> Касса: <?php echo number_format($row['final_cash'] ?? $row['initial_cash'], 2); ?> руб.</p>
                </div>
                <div class="shift-actions">
                    <a href="shifts.php?close=<?php echo $row['id']; ?>" class="btn-shift-action btn-close">
                        <i class="fas fa-stop"></i> Закрыть
                    </a>
                    <a href="shifts.php?edit=<?php echo $row['id']; ?>" class="btn-shift-action btn-edit">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="no-shifts">
                <i class="fas fa-clock" style="font-size: 3em; opacity: 0.3;"></i>
                <p>Нет активных смен</p>
                <a href="shifts.php" class="btn-start-shift">
                    <i class="fas fa-play"></i> Открыть смену
                </a>
            </div>
            <?php endif; ?>
            <?php } catch (Exception $e) { ?>
            <div class="no-shifts">
                <i class="fas fa-exclamation-triangle" style="font-size: 3em; opacity: 0.3;"></i>
                <p>Ошибка загрузки данных</p>
            </div>
            <?php } ?>
            
            <div class="view-all">
                <a href="shifts.php" class="btn-view-all">
                    <i class="fas fa-eye"></i> Все смены
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- VIP гости -->
    <div class="vip-guests">
        <h3><i class="fas fa-star"></i> Наши лучшие гости</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Гость</th>
                    <th>Посещения</th>
                    <th>Всего потрачено</th>
                    <th>Телефон</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM guests WHERE total_spent > 0 ORDER BY total_spent DESC LIMIT 5");
                    while ($row = $stmt->fetch()):
                        $vipLevel = $row['total_spent'] > 50000 ? 'VIP Diamond' : 
                                   ($row['total_spent'] > 20000 ? 'VIP Gold' : 'VIP Silver');
                ?>
                <tr>
                    <td>
                        <strong><?php echo escape($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                    </td>
                    <td><?php echo escape($row['visits_count']); ?></td>
                    <td>
                        <span class="vip-amount">
                            <?php echo number_format($row['total_spent'], 2); ?> руб.
                        </span>
                    </td>
                    <td><?php echo escape($row['phone']); ?></td>
                    <td>
                        <span class="vip-badge vip-<?php echo strtolower(str_replace(' ', '-', $vipLevel)); ?>">
                            <?php echo $vipLevel; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php } catch (Exception $e) { ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--secondary-light);">
                        <i class="fas fa-exclamation-triangle"></i> Нет данных для отображения
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div class="view-all">
            <a href="guests.php" class="btn-view-all">
                <i class="fas fa-eye"></i> Все гости
            </a>
        </div>
    </div>
</div>

<!-- Информация о системе -->
<div class="system-info">
    <h3><i class="fas fa-info-circle"></i> Информация о системе</h3>
    <div class="info-grid">
        <div class="info-card">
            <h4><i class="fas fa-user-shield"></i> Безопасность</h4>
            <p>Система защищена авторизацией и ролевым доступом</p>
        </div>
        <div class="info-card">
            <h4><i class="fas fa-database"></i> База данных</h4>
            <p>project_Bolshakov на сервере 134.90.167.42:10306</p>
        </div>
        <div class="info-card">
            <h