<?php
// order_details.php - ИСПРАВЛЕННАЯ ВЕРСИЯ, БЕЗ EMAIL
ob_start();

require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    ob_end_clean();
    header('Location: login.php');
    exit;
}

// Получение ID заказа из URL
$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    ob_end_clean();
    header('Location: orders.php');
    exit;
}

// Получаем информацию о заказе
$order = null;
$details = [];

try {
    // Получаем информацию о заказе
    $stmt = $pdo->prepare("
        SELECT o.*, g.first_name, g.last_name, g.phone 
        FROM orders o 
        LEFT JOIN guests g ON o.guest_id = g.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        ob_end_clean();
        header('Location: orders.php');
        exit;
    }
    
    // Получаем детали заказа (если есть)
    $stmt = $pdo->prepare("
        SELECT od.*, mi.name, mi.price as menu_price 
        FROM order_details od 
        JOIN menu_items mi ON od.menu_item_id = mi.id 
        WHERE od.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $details = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}

// Получаем информацию для отображения
$order_type_text = $order['order_type'] == 'game' ? '🎰 Игра' : '🍸 Бар';
$status_class = strtolower($order['status'] ?? 'pending');
$status_text = [
    'pending' => 'В ожидании',
    'completed' => 'Завершен',
    'cancelled' => 'Отменен'
];
?>

<div class="container">
    <h1><i class="fas fa-list-alt"></i> Детали заказа #<?php echo $order_id; ?></h1>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Информация о заказе</h3>
            <div class="action-buttons">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Назад к заказам
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Основная информация -->
            <div class="order-summary">
                <div class="summary-row">
                    <div class="summary-col">
                        <h4>Информация о госте</h4>
                        <?php if ($order['guest_id']): ?>
                            <p><strong><?php echo escape($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                            <p><i class="fas fa-phone"></i> <?php echo escape($order['phone'] ?? 'Не указан'); ?></p>
                        <?php else: ?>
                            <p><em>Гость (без регистрации)</em></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="summary-col">
                        <h4>Детали заказа</h4>
                        <p><strong>Тип:</strong> <?php echo $order_type_text; ?></p>
                        <p><strong>Дата и время:</strong> <?php echo date('d.m.Y H:i:s', strtotime($order['order_time'])); ?></p>
                        <p><strong>Статус:</strong> 
                            <span class="status status-<?php echo $status_class; ?>">
                                <?php echo $status_text[$status_class] ?? $order['status']; ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if ($order['notes']): ?>
                <div class="summary-row">
                    <div class="summary-col">
                        <h4>Примечания</h4>
                        <div class="notes-box">
                            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <div class="summary-col">
                        <h4>Финансовая информация</h4>
                        <div class="total-amount">
                            <span class="total-label">Общая сумма:</span>
                            <span class="total-value"><?php echo number_format($order['total_amount'], 0); ?> ₽</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Состав заказа -->
            <?php if (!empty($details)): ?>
            <div class="order-composition">
                <h3>Состав заказа</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Позиция</th>
                                <th>Цена за единицу</th>
                                <th>Количество</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($details as $index => $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $total += $item_total;
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                <td class="amount-cell"><?php echo number_format($item['price'], 0); ?> ₽</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="amount-cell"><?php echo number_format($item_total, 0); ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Итого:</strong></td>
                                <td class="amount-cell"><strong><?php echo number_format($total, 0); ?> ₽</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.order-summary {
    background: rgba(26, 26, 26, 0.6);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
}

.summary-row {
    display: flex;
    gap: 30px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.summary-col {
    flex: 1;
    min-width: 250px;
}

.summary-col h4 {
    color: var(--accent);
    margin-bottom: 15px;
}

.notes-box {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 8px;
    padding: 15px;
    color: var(--secondary-light);
}

.total-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: rgba(26, 26, 26, 0.8);
    border-radius: 8px;
}

.total-label {
    color: var(--secondary-light);
    font-size: 1.1rem;
}

.total-value {
    color: var(--accent);
    font-size: 1.8rem;
    font-weight: bold;
}

.order-composition {
    margin-top: 30px;
}

.order-composition h3 {
    color: var(--accent);
    margin-bottom: 20px;
}

.text-right {
    text-align: right;
}
</style>

<?php 
ob_end_flush();
require_once 'footer.php'; 
?>