<?php
// index.php - ГЛАВНАЯ СТРАНИЦА (БЕЗ РАЗВЛЕЧЕНИЙ)
require_once 'config.php';
require_once 'header.php';

$is_guest = isGuest();
$user_role = getUserRole();
$user_name = getUserName();

// Получаем статистику
$stats = [];

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests");
    $stats['guests'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests WHERE total_spent > 10000");
    $stats['vip_guests'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT first_name, last_name, total_spent FROM guests ORDER BY total_spent DESC LIMIT 1");
    $stats['top_guest'] = $stmt->fetch();
    
} catch (Exception $e) {
    $stats['guests'] = 0;
    $stats['vip_guests'] = 0;
    $stats['top_guest'] = null;
}

if (!isGuest()) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees");
        $stats['employees'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM employees WHERE is_active = 1");
        $stats['active_employees'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM menu_items WHERE is_available = 1");
        $stats['menu_items'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders");
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_time) = ?");
        $stmt->execute([$today]);
        $stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['orders_count'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM shifts WHERE status = 'active'");
        $stats['active_shifts'] = $stmt->fetch()['count'];
        
    } catch (Exception $e) {
        $stats['employees'] = 0;
        $stats['active_employees'] = 0;
        $stats['menu_items'] = 0;
        $stats['total_revenue'] = 0;
        $stats['today_revenue'] = 0;
        $stats['orders_count'] = 0;
        $stats['active_shifts'] = 0;
    }
} else {
    $stats['employees'] = '???';
    $stats['active_employees'] = '???';
    $stats['menu_items'] = '???';
    $stats['total_revenue'] = '???';
    $stats['today_revenue'] = '???';
    $stats['orders_count'] = '???';
    $stats['active_shifts'] = '???';
}

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
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, rgba(44, 11, 43, 0.8), rgba(26, 26, 26, 0.9));
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-3px);
    border-color: var(--accent);
}

.stat-card h3 {
    color: var(--accent);
    margin-bottom: 10px;
    font-size: 1rem;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}

.quick-access-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.quick-access-card {
    background: rgba(26, 26, 26, 0.7);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    border: 1px solid rgba(212, 175, 55, 0.1);
    transition: all 0.3s;
}

.quick-access-card:hover {
    transform: translateY(-3px);
    border-color: var(--accent);
}

.card-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, var(--secondary), var(--primary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.card-content h4 {
    color: white;
    margin-bottom: 5px;
}

.card-content p {
    color: var(--secondary-light);
    font-size: 0.85rem;
}

.welcome-message {
    background: linear-gradient(135deg, rgba(44, 11, 43, 0.9), rgba(26, 26, 26, 0.95));
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    border: 2px solid var(--secondary);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.welcome-content h1 {
    color: var(--secondary);
    font-size: 2rem;
}

.welcome-stats {
    display: flex;
    gap: 15px;
}

.stat-badge {
    background: rgba(212, 175, 55, 0.2);
    border: 1px solid var(--accent);
    border-radius: 10px;
    padding: 8px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.activity-card {
    background: rgba(26, 26, 26, 0.8);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.activity-card h3 {
    color: var(--accent);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.activity-list {
    list-style: none;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.vip-guests-table {
    width: 100%;
    border-collapse: collapse;
}

.vip-guests-table th,
.vip-guests-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.vip-guests-table th {
    color: var(--accent);
}
</style>

<div class="container">
    <!-- Приветственная секция -->
    <div class="welcome-message">
        <div class="welcome-content">
            <h1><i class="fas <?php echo $greeting_icon; ?>"></i> <?php echo $greeting; ?>, <?php echo escape($user_name); ?>!</h1>
            <p style="margin-top: 10px;">
                <i class="fas fa-calendar"></i> <?php echo date('d.m.Y'); ?> | 
                <i class="fas fa-clock"></i> <?php echo date('H:i'); ?>
            </p>
        </div>
        <div class="welcome-stats">
            <div class="stat-badge"><i class="fas fa-users"></i> <?php echo $stats['guests']; ?> гостей</div>
            <?php if (!isGuest()): ?>
            <div class="stat-badge"><i class="fas fa-ruble-sign"></i> <?php echo number_format($stats['today_revenue'], 0); ?> ₽ сегодня</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><i class="fas fa-user-tie"></i> VIP Гости</h3>
            <div class="value"><?php echo $stats['guests']; ?></div>
            <small>VIP: <?php echo $stats['vip_guests']; ?></small>
        </div>
        
        <?php if (!isGuest()): ?>
        <div class="stat-card">
            <h3><i class="fas fa-users"></i> Персонал</h3>
            <div class="value"><?php echo $stats['employees']; ?></div>
            <small>Активных: <?php echo $stats['active_employees']; ?></small>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-utensils"></i> Меню</h3>
            <div class="value"><?php echo $stats['menu_items']; ?></div>
            <small>позиций доступно</small>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-ruble-sign"></i> Выручка</h3>
            <div class="value"><?php echo number_format($stats['total_revenue'], 0); ?> ₽</div>
            <small>За сегодня: <?php echo number_format($stats['today_revenue'], 0); ?> ₽</small>
        </div>
        <?php else: ?>
        <div class="stat-card">
            <h3><i class="fas fa-lock"></i> Персонал</h3>
            <div class="value">❓</div>
            <small><a href="login.php" style="color: var(--accent);">Войдите</a> для просмотра</small>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-lock"></i> Финансы</h3>
            <div class="value">❓</div>
            <small><a href="login.php" style="color: var(--accent);">Войдите</a> для просмотра</small>
        </div>
        <div class="stat-card">
            <h3><i class="fas fa-clock"></i> Смены</h3>
            <div class="value">❓</div>
            <small><a href="login.php" style="color: var(--accent);">Войдите</a> для просмотра</small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Активность (только для авторизованных) -->
    <?php if (!isGuest()): ?>
    <div class="activity-grid">
        <div class="activity-card">
            <h3><i class="fas fa-history"></i> Последние заказы</h3>
            <div class="activity-list">
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT o.*, g.first_name, g.last_name 
                        FROM orders o 
                        LEFT JOIN guests g ON o.guest_id = g.id 
                        ORDER BY o.order_time DESC 
                        LIMIT 5
                    ");
                    while ($order = $stmt->fetch()):
                ?>
                <div class="activity-item">
                    <div>
                        <span class="order-type order-type-<?php echo $order['order_type']; ?>">
                            <?php echo $order['order_type'] == 'bar' ? '🍸 Бар' : '🎰 Игра'; ?>
                        </span>
                        <span style="margin-left: 10px;">
                            <?php echo escape($order['first_name'] ?? 'Гость'); ?>
                        </span>
                    </div>
                    <div class="amount-cell"><?php echo number_format($order['total_amount'], 0); ?> ₽</div>
                </div>
                <?php endwhile; ?>
                <?php } catch (Exception $e) { ?>
                <div class="activity-item">Нет данных</div>
                <?php } ?>
            </div>
        </div>
        
        <div class="activity-card">
            <h3><i class="fas fa-clock"></i> Активные смены</h3>
            <div class="activity-list">
                <?php
                try {
                    $stmt = $pdo->query("
                        SELECT s.*, e.first_name, e.last_name 
                        FROM shifts s 
                        JOIN employees e ON s.employee_id = e.id 
                        WHERE s.status = 'active' 
                        LIMIT 3
                    ");
                    while ($shift = $stmt->fetch()):
                ?>
                <div class="activity-item">
                    <div>
                        <strong><?php echo escape($shift['first_name'] . ' ' . $shift['last_name']); ?></strong>
                        <div style="font-size: 0.8rem; color: var(--secondary-light);">
                            с <?php echo date('H:i', strtotime($shift['start_time'])); ?>
                        </div>
                    </div>
                    <div class="amount-cell"><?php echo number_format($shift['initial_cash'], 0); ?> ₽</div>
                </div>
                <?php endwhile; ?>
                <?php } catch (Exception $e) { ?>
                <div class="activity-item">Нет активных смен</div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Быстрый доступ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Быстрый доступ</h3>
        </div>
        <div class="quick-access-grid">
            <a href="guests.php" class="quick-access-card">
                <div class="card-icon"><i class="fas fa-user-tie"></i></div>
                <div class="card-content">
                    <h4>Гости</h4>
                    <p>Просмотр гостей</p>
                </div>
            </a>
            
            <a href="menu_guest.php" class="quick-access-card">
                <div class="card-icon"><i class="fas fa-pizza-slice"></i></div>
                <div class="card-content">
                    <h4>Заказать еду</h4>
                    <p>Меню ресторана</p>
                </div>
            </a>
            
            <?php if (!isGuest()): ?>
            <a href="employees.php" class="quick-access-card">
                <div class="card-icon"><i class="fas fa-users"></i></div>
                <div class="card-content">
                    <h4>Сотрудники</h4>
                    <p>Управление персоналом</p>
                </div>
            </a>
            <a href="orders.php" class="quick-access-card">
                <div class="card-icon"><i class="fas fa-credit-card"></i></div>
                <div class="card-content">
                    <h4>Заказы</h4>
                    <p>Управление заказами</p>
                </div>
            </a>
            <a href="shifts.php" class="quick-access-card">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-content">
                    <h4>Смены</h4>
                    <p>Управление сменами</p>
                </div>
            </a>
            <?php else: ?>
            <div class="quick-access-card guest-restricted">
                <div class="card-icon"><i class="fas fa-lock"></i></div>
                <div class="card-content">
                    <h4>Доступ ограничен</h4>
                    <p><a href="login.php" style="color: var(--accent);">Войдите</a> для полного доступа</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Топ гостей -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Топ гостей по тратам</h3>
            <a href="guests.php" class="btn btn-primary btn-sm">Все гости →</a>
        </div>
        <div class="card-body">
            <table class="vip-guests-table">
                <thead>
                    <tr><th>Гость</th><th>Посещений</th><th>Потрачено</th></tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT first_name, last_name, visits_count, total_spent FROM guests ORDER BY total_spent DESC LIMIT 5");
                        while ($guest = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><strong><?php echo escape($guest['first_name'] . ' ' . $guest['last_name']); ?></strong></td>
                        <td><?php echo $guest['visits_count']; ?></td>
                        <td class="amount-cell"><?php echo number_format($guest['total_spent'], 0); ?> ₽</td>
                    </tr>
                    <?php endwhile; ?>
                    <?php } catch (Exception $e) { ?>
                    <tr><td colspan="3">Нет данных</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>