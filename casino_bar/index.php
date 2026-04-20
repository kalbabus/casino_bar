<?php
// index.php - ГЛАВНАЯ СТРАНИЦА ЭЛИТНОЕ КАЗИНО
require_once 'config.php';
require_once 'header.php';

$is_guest = isGuest();
$user_role = getUserRole();
$user_name = getUserName();

// Получаем статистику
$stats = [];

// Общая статистика (доступна всем)
try {
    // Количество гостей
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests");
    $stats['guests'] = $stmt->fetch()['count'];
    
    // VIP гости (потратили больше 10000)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests WHERE total_spent > 10000");
    $stats['vip_guests'] = $stmt->fetch()['count'];
    
    // Самый активный гость
    $stmt = $pdo->query("SELECT first_name, last_name, total_spent FROM guests ORDER BY total_spent DESC LIMIT 1");
    $stats['top_guest'] = $stmt->fetch();
    
} catch (Exception $e) {
    $stats['guests'] = 0;
    $stats['vip_guests'] = 0;
    $stats['top_guest'] = null;
}

// Статистика только для авторизованных
if (!isGuest()) {
    try {
        // Количество сотрудников
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
        $stats['employees'] = $stmt->fetch()['count'];
        
        // Количество активных сотрудников
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE is_active = 1");
        $stats['active_employees'] = $stmt->fetch()['count'];
        
        // Количество позиций в меню
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1");
        $stats['menu_items'] = $stmt->fetch()['count'];
        
        // Общая выручка
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        // Выручка за сегодня
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_time) = ?");
        $stmt->execute([$today]);
        $stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        // Выручка за эту неделю
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_time) >= ?");
        $stmt->execute([$weekStart]);
        $stats['week_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        // Количество заказов
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders_count'] = $stmt->fetch()['count'];
        
        // Заказов сегодня
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(order_time) = ?");
        $stmt->execute([$today]);
        $stats['today_orders'] = $stmt->fetch()['count'] ?? 0;
        
        // Активные смены
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM shifts WHERE status = 'active'");
        $stats['active_shifts'] = $stmt->fetch()['count'];
        
        // Баланс кассы (сумма начальных касс активных смен)
        $stmt = $pdo->query("SELECT SUM(initial_cash) as total FROM shifts WHERE status = 'active'");
        $stats['cash_balance'] = $stmt->fetch()['total'] ?? 0;
        
        // Самый популярный товар
        $stmt = $pdo->query("
            SELECT mi.name, SUM(od.quantity) as total_quantity 
            FROM order_details od 
            JOIN menu_items mi ON od.menu_item_id = mi.id 
            GROUP BY mi.id 
            ORDER BY total_quantity DESC 
            LIMIT 1
        ");
        $stats['top_item'] = $stmt->fetch();
        
    } catch (Exception $e) {
        $stats['employees'] = 0;
        $stats['active_employees'] = 0;
        $stats['menu_items'] = 0;
        $stats['total_revenue'] = 0;
        $stats['today_revenue'] = 0;
        $stats['week_revenue'] = 0;
        $stats['orders_count'] = 0;
        $stats['today_orders'] = 0;
        $stats['active_shifts'] = 0;
        $stats['cash_balance'] = 0;
        $stats['top_item'] = null;
    }
} else {
    $stats['employees'] = '???';
    $stats['active_employees'] = '???';
    $stats['menu_items'] = '???';
    $stats['total_revenue'] = '???';
    $stats['today_revenue'] = '???';
    $stats['week_revenue'] = '???';
    $stats['orders_count'] = '???';
    $stats['today_orders'] = '???';
    $stats['active_shifts'] = '???';
    $stats['cash_balance'] = '???';
    $stats['top_item'] = null;
}

// Получаем последние заказы (только для авторизованных)
$recent_orders = [];
if (!isGuest()) {
    try {
        $stmt = $pdo->query("
            SELECT o.*, g.first_name, g.last_name 
            FROM orders o 
            LEFT JOIN guests g ON o.guest_id = g.id 
            ORDER BY o.order_time DESC 
            LIMIT 5
        ");
        $recent_orders = $stmt->fetchAll();
    } catch (Exception $e) {
        $recent_orders = [];
    }
}

// Получаем активные смены
$active_shifts_list = [];
if (!isGuest()) {
    try {
        $stmt = $pdo->query("
            SELECT s.*, e.first_name, e.last_name, e.position 
            FROM shifts s 
            JOIN employees e ON s.employee_id = e.id 
            WHERE s.status = 'active' 
            ORDER BY s.start_time DESC
        ");
        $active_shifts_list = $stmt->fetchAll();
    } catch (Exception $e) {
        $active_shifts_list = [];
    }
}

// Получаем топ гостей
$top_guests = [];
try {
    $stmt = $pdo->query("
        SELECT first_name, last_name, total_spent, visits_count 
        FROM guests 
        ORDER BY total_spent DESC 
        LIMIT 5
    ");
    $top_guests = $stmt->fetchAll();
} catch (Exception $e) {
    $top_guests = [];
}

// Приветствие в зависимости от времени
$current_hour = date('H');
if ($current_hour >= 5 && $current_hour < 12) {
    $greeting = 'Доброе утро';
    $greeting_icon = 'fa-sun';
} elseif ($current_hour >= 12 && $current_hour < 18) {
    $greeting = 'Добрый день';
    $greeting_icon = 'fa-sun';
} elseif ($current_hour >= 18 && $current_hour < 23) {
    $greeting = 'Добрый вечер';
    $greeting_icon = 'fa-moon';
} else {
    $greeting = 'Доброй ночи';
    $greeting_icon = 'fa-star';
}
?>

<style>
/* Дополнительные стили для главной страницы */
.greeting-icon {
    font-size: 2rem;
    margin-right: 15px;
    color: var(--accent);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card-modern {
    background: linear-gradient(135deg, rgba(44, 11, 43, 0.8), rgba(26, 26, 26, 0.9));
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
}

.stat-card-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--secondary), var(--accent));
}

.stat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 15px;
}

.stat-header i {
    font-size: 2rem;
    color: var(--accent);
    opacity: 0.7;
}

.stat-header h3 {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: white;
    margin: 10px 0;
}

.stat-sub {
    color: var(--secondary-light);
    font-size: 0.85rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 10px;
    margin-top: 10px;
}

.guest-stat {
    background: linear-gradient(135deg, rgba(199, 21, 133, 0.1), rgba(139, 0, 0, 0.1));
}

.section-title {
    font-size: 1.5rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--accent);
}

.section-title i {
    font-size: 1.8rem;
}

.activity-list {
    list-style: none;
}

.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-type {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.activity-type.bar {
    background: rgba(40, 167, 69, 0.2);
    color: #4caf50;
}

.activity-type.game {
    background: rgba(199, 21, 133, 0.2);
    color: var(--secondary);
}

.activity-amount {
    font-weight: bold;
    color: var(--accent);
}

.shift-card-small {
    background: rgba(26, 26, 26, 0.6);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    border-left: 3px solid var(--accent);
}

.shift-card-small h4 {
    margin: 0 0 5px 0;
    font-size: 1rem;
}

.shift-card-small p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--secondary-light);
}

.top-guest-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.top-guest-rank {
    width: 30px;
    height: 30px;
    background: rgba(212, 175, 55, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: var(--accent);
}

.top-guest-rank.rank-1 {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: #333;
}

.top-guest-info {
    flex: 1;
    margin-left: 12px;
}

.top-guest-name {
    font-weight: bold;
}

.top-guest-amount {
    color: var(--accent);
    font-weight: bold;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--secondary-light);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.3;
}

.guest-restricted-card {
    opacity: 0.7;
    position: relative;
    cursor: not-allowed;
}

.guest-restricted-card .lock-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 1.8rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .two-columns {
        grid-template-columns: 1fr;
    }
}

.two-columns {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.quick-link {
    background: rgba(26, 26, 26, 0.6);
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s;
    border: 1px solid rgba(212, 175, 55, 0.1);
}

.quick-link:hover {
    transform: translateY(-3px);
    border-color: var(--accent);
    background: rgba(212, 175, 55, 0.1);
}

.quick-link i {
    font-size: 1.5rem;
    color: var(--accent);
    margin-bottom: 8px;
    display: block;
}

.quick-link span {
    font-size: 0.85rem;
}

.btn-view-all {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 20px;
    background: rgba(212, 175, 55, 0.2);
    border-radius: 8px;
    color: var(--accent);
    text-align: center;
    transition: all 0.3s;
}

.btn-view-all:hover {
    background: rgba(212, 175, 55, 0.4);
}
</style>

<div class="container">
    <!-- Приветственная секция -->
    <div class="welcome-message">
        <div style="display: flex; align-items: center;">
            <i class="fas <?php echo $greeting_icon; ?> greeting-icon"></i>
            <div>
                <h1 style="margin: 0;"><?php echo $greeting; ?>, <?php echo escape($user_name); ?>!</h1>
                <p class="welcome-subtitle" style="margin-top: 8px;">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d.m.Y'); ?>
                    <span class="separator">•</span>
                    <i class="fas fa-clock"></i> <?php echo date('H:i'); ?>
                    <span class="separator">•</span>
                    <i class="fas fa-chart-line"></i> Система управления премиальным заведением
                </p>
            </div>
        </div>
        <div class="welcome-stats">
            <div class="stat-badge">
                <i class="fas fa-users"></i>
                <span><?php echo $stats['guests']; ?> гостей</span>
            </div>
            <?php if (!isGuest() && isset($stats['today_revenue']) && is_numeric($stats['today_revenue'])): ?>
            <div class="stat-badge">
                <i class="fas fa-ruble-sign"></i>
                <span><?php echo number_format($stats['today_revenue'], 0, '.', ' '); ?> ₽ сегодня</span>
            </div>
            <?php endif; ?>
            <?php if (isGuest()): ?>
            <div class="stat-badge">
                <i class="fas fa-lock"></i>
                <span>Гостевой режим</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Статистика -->
    <div class="stats-grid">
        <!-- Гости -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-user-tie"></i> VIP Гости</h3>
                <i class="fas fa-crown"></i>
            </div>
            <div class="stat-value"><?php echo $stats['guests']; ?></div>
            <div class="stat-sub">Из них VIP: <?php echo $stats['vip_guests']; ?></div>
            <?php if ($stats['top_guest']): ?>
            <div class="stat-sub">🏆 Лидер: <?php echo escape($stats['top_guest']['first_name'] . ' ' . $stats['top_guest']['last_name']); ?></div>
            <?php endif; ?>
        </div>

        <?php if (!isGuest()): ?>
        <!-- Сотрудники -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-users"></i> Персонал</h3>
                <i class="fas fa-id-badge"></i>
            </div>
            <div class="stat-value"><?php echo $stats['employees']; ?></div>
            <div class="stat-sub">Активных: <?php echo $stats['active_employees']; ?></div>
        </div>

        <!-- Меню -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-utensils"></i> Меню</h3>
                <i class="fas fa-wine-glass-alt"></i>
            </div>
            <div class="stat-value"><?php echo $stats['menu_items']; ?></div>
            <div class="stat-sub">Доступных позиций</div>
            <?php if ($stats['top_item']): ?>
            <div class="stat-sub">⭐ Хит: <?php echo escape($stats['top_item']['name']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Финансы -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-chart-line"></i> Финансы</h3>
                <i class="fas fa-ruble-sign"></i>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_revenue'], 0, '.', ' '); ?> ₽</div>
            <div class="stat-sub">
                Сегодня: <?php echo number_format($stats['today_revenue'], 0, '.', ' '); ?> ₽<br>
                За неделю: <?php echo number_format($stats['week_revenue'], 0, '.', ' '); ?> ₽
            </div>
        </div>

        <!-- Заказы -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-shopping-cart"></i> Заказы</h3>
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-value"><?php echo $stats['orders_count']; ?></div>
            <div class="stat-sub">Сегодня: <?php echo $stats['today_orders']; ?> заказов</div>
        </div>

        <!-- Смены -->
        <div class="stat-card-modern">
            <div class="stat-header">
                <h3><i class="fas fa-clock"></i> Смены</h3>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value"><?php echo $stats['active_shifts']; ?></div>
            <div class="stat-sub">Активных смен | Касса: <?php echo number_format($stats['cash_balance'], 0, '.', ' '); ?> ₽</div>
        </div>
        <?php else: ?>
        <!-- Заглушки для гостей -->
        <div class="stat-card-modern guest-restricted-card">
            <div class="lock-overlay"><i class="fas fa-lock"></i></div>
            <div class="stat-header">
                <h3><i class="fas fa-users"></i> Персонал</h3>
                <i class="fas fa-id-badge"></i>
            </div>
            <div class="stat-value">❓</div>
            <div class="stat-sub"><a href="login.php">Войдите</a> для просмотра</div>
        </div>
        <div class="stat-card-modern guest-restricted-card">
            <div class="lock-overlay"><i class="fas fa-lock"></i></div>
            <div class="stat-header">
                <h3><i class="fas fa-chart-line"></i> Финансы</h3>
                <i class="fas fa-ruble-sign"></i>
            </div>
            <div class="stat-value">❓</div>
            <div class="stat-sub"><a href="login.php">Войдите</a> для просмотра</div>
        </div>
        <div class="stat-card-modern guest-restricted-card">
            <div class="lock-overlay"><i class="fas fa-lock"></i></div>
            <div class="stat-header">
                <h3><i class="fas fa-clock"></i> Смены</h3>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value">❓</div>
            <div class="stat-sub"><a href="login.php">Войдите</a> для просмотра</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Две колонки с активностью -->
    <div class="two-columns">
        <!-- Последние заказы -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Последние заказы</h3>
                <?php if (!isGuest()): ?>
                <a href="orders.php" class="btn btn-primary btn-sm">Все заказы <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!isGuest() && !empty($recent_orders)): ?>
                    <ul class="activity-list">
                        <?php foreach ($recent_orders as $order): ?>
                        <li class="activity-item">
                            <div>
                                <span class="activity-type <?php echo $order['order_type']; ?>">
                                    <?php echo $order['order_type'] == 'bar' ? '🍸 Бар' : '🎰 Игра'; ?>
                                </span>
                                <span style="margin-left: 10px;">
                                    <?php if ($order['first_name']): ?>
                                        <?php echo escape($order['first_name'] . ' ' . $order['last_name']); ?>
                                    <?php else: ?>
                                        <em>Гость</em>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div>
                                <span class="activity-amount"><?php echo number_format($order['total_amount'], 0); ?> ₽</span>
                                <span style="font-size: 0.8rem; color: var(--secondary-light); margin-left: 10px;">
                                    <?php echo date('H:i', strtotime($order['order_time'])); ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif (!isGuest()): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Нет заказов</p>
                        <a href="orders.php?add" class="btn btn-primary">Создать заказ</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-lock"></i>
                        <p>Авторизуйтесь для просмотра заказов</p>
                        <a href="login.php" class="btn btn-primary">Войти</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Активные смены -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Активные смены</h3>
                <?php if (!isGuest()): ?>
                <a href="shifts.php?new" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Открыть смену</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!isGuest() && !empty($active_shifts_list)): ?>
                    <?php foreach ($active_shifts_list as $shift): ?>
                    <div class="shift-card-small">
                        <h4><?php echo escape($shift['first_name'] . ' ' . $shift['last_name']); ?></h4>
                        <p><i class="fas fa-briefcase"></i> <?php echo escape($shift['position']); ?></p>
                        <p><i class="fas fa-clock"></i> Начало: <?php echo date('H:i', strtotime($shift['start_time'])); ?></p>
                        <p><i class="fas fa-cash-register"></i> Касса: <?php echo number_format($shift['initial_cash'], 0); ?> ₽</p>
                        <a href="shifts.php?close=<?php echo $shift['id']; ?>" class="btn btn-warning btn-sm" style="margin-top: 10px;">Закрыть смену</a>
                    </div>
                    <?php endforeach; ?>
                <?php elseif (!isGuest()): ?>
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>Нет активных смен</p>
                        <a href="shifts.php?new" class="btn btn-success">Открыть смену</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-lock"></i>
                        <p>Авторизуйтесь для управления сменами</p>
                        <a href="login.php" class="btn btn-primary">Войти</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Топ гостей -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Топ гостей по тратам</h3>
            <a href="guests.php" class="btn btn-primary btn-sm">Все гости <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
            <?php if (!empty($top_guests)): ?>
                <?php foreach ($top_guests as $index => $guest): ?>
                <div class="top-guest-item">
                    <div class="top-guest-rank rank-<?php echo $index + 1; ?>">
                        <?php echo $index + 1; ?>
                    </div>
                    <div class="top-guest-info">
                        <div class="top-guest-name"><?php echo escape($guest['first_name'] . ' ' . $guest['last_name']); ?></div>
                        <div style="font-size: 0.8rem; color: var(--secondary-light);">
                            <i class="fas fa-calendar-check"></i> <?php echo $guest['visits_count']; ?> посещений
                        </div>
                    </div>
                    <div class="top-guest-amount">
                        <?php echo number_format($guest['total_spent'], 0, '.', ' '); ?> ₽
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Нет данных о гостях</p>
                    <?php if (!isGuest()): ?>
                    <a href="guests.php?add" class="btn btn-primary">Добавить гостя</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Быстрые ссылки -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Быстрый доступ</h3>
        </div>
        <div class="quick-links">
            <a href="guests.php" class="quick-link">
                <i class="fas fa-user-tie"></i>
                <span>Гости</span>
            </a>
            <?php if (!isGuest()): ?>
            <a href="employees.php" class="quick-link">
                <i class="fas fa-users"></i>
                <span>Сотрудники</span>
            </a>
            <a href="menu_items.php" class="quick-link">
                <i class="fas fa-utensils"></i>
                <span>Меню</span>
            </a>
            <a href="orders.php" class="quick-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Заказы</span>
            </a>
            <a href="shifts.php" class="quick-link">
                <i class="fas fa-clock"></i>
                <span>Смены</span>
            </a>
            <?php else: ?>
            <a href="login.php" class="quick-link">
                <i class="fas fa-sign-in-alt"></i>
                <span>Войти</span>
            </a>
            <div class="quick-link" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-lock"></i>
                <span>Сотрудники (требуется вход)</span>
            </div>
            <div class="quick-link" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-lock"></i>
                <span>Меню (требуется вход)</span>
            </div>
            <div class="quick-link" style="opacity: 0.5; cursor: not-allowed;">
                <i class="fas fa-lock"></i>
                <span>Заказы (требуется вход)</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Информация о системе -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> О системе</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <i class="fas fa-database" style="color: var(--accent);"></i>
                <strong>База данных:</strong>
                <p style="color: var(--secondary-light); font-size: 0.9rem;">MySQL • project_Bolshakov</p>
            </div>
            <div>
                <i class="fas fa-shield-alt" style="color: var(--accent);"></i>
                <strong>Безопасность:</strong>
                <p style="color: var(--secondary-light); font-size: 0.9rem;">Ролевой доступ • Капча • Хэширование паролей</p>
            </div>
            <div>
                <i class="fas fa-chart-line" style="color: var(--accent);"></i>
                <strong>Версия:</strong>
                <p style="color: var(--secondary-light); font-size: 0.9rem;">Элитное Казино v2.0</p>
            </div>
        </div>
    </div>
</div>
<a href="menu_guest.php" class="quick-access-card">
    <div class="card-icon"><i class="fas fa-pizza-slice"></i></div>
    <div class="card-content">
        <h4>Заказать еду</h4>
        <p>Блюда и напитки</p>
    </div>
</a>

<?php require_once 'footer.php'; ?>