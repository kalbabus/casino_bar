<?php
// orders.php - ИСПРАВЛЕННАЯ ВЕРСИЯ
require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

// Обработка сообщений
$message = '';
$error = '';

if (isset($_GET['success'])) {
    $order_id = $_GET['id'] ?? '';
    $message = 'Заказ #' . $order_id . ' успешно создан!';
}

if (isset($_GET['error'])) {
    $error = urldecode($_GET['error']);
}

// Обработка удаления заказа
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Сначала удаляем детали заказа (ON DELETE CASCADE должно сработать)
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Заказ удален';
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
}

// Получаем список заказов
$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.*, g.first_name, g.last_name 
        FROM orders o 
        LEFT JOIN guests g ON o.guest_id = g.id 
        ORDER BY o.order_time DESC 
        LIMIT 50
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}

// Получаем список гостей для формы
$guests = [];
try {
    $guests_stmt = $pdo->query("SELECT id, first_name, last_name FROM guests ORDER BY last_name");
    $guests = $guests_stmt->fetchAll();
} catch (PDOException $e) {
    // Игнорируем ошибку
}
?>

<div class="container">
    <h1><i class="fas fa-credit-card"></i> Заказы</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Список заказов (<?php echo count($orders); ?>)</h3>
            <a href="?add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Новый заказ
            </a>
        </div>
        
        <div class="card-body">
            <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Гость</th>
                            <th>Тип</th>
                            <th>Сумма</th>
                            <th>Время</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row): ?>
                        <tr>
                            <td>#<?php echo escape($row['id']); ?></td>
                            <td>
                                <?php if ($row['guest_id']): ?>
                                    <?php echo escape($row['first_name'] . ' ' . $row['last_name']); ?>
                                <?php else: ?>
                                    <em>Гость (без регистрации)</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="order-type order-type-<?php echo $row['order_type']; ?>">
                                    <?php echo $row['order_type'] == 'game' ? '🎰 Игра' : '🍸 Бар'; ?>
                                </span>
                            </td>
                            <td class="amount-cell"><?php echo number_format($row['total_amount'], 0); ?> ₽</td>
                            <td><?php echo date('d.m.Y H:i', strtotime($row['order_time'])); ?></td>
                            <td>
                                <span class="status status-<?php echo strtolower($row['status']); ?>">
                                    <?php 
                                    $status_text = [
                                        'pending' => 'В ожидании',
                                        'completed' => 'Завершен',
                                        'cancelled' => 'Отменен'
                                    ];
                                    echo $status_text[$row['status']] ?? $row['status']; 
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order_details.php?order_id=<?php echo $row['id']; ?>" class="btn-action btn-view" title="Просмотреть">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить заказ #<?php echo $row['id']; ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-credit-card" style="font-size: 4rem; opacity: 0.3;"></i>
                <p>Нет данных о заказах</p>
                <a href="?add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Создать первый заказ
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Форма для быстрого создания заказа -->
    <?php if (isset($_GET['add'])): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> Создание нового заказа</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="process_order.php">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="guest_id">Гость:</label>
                        <select id="guest_id" name="guest_id" class="form-control">
                            <option value="">Выберите гостя</option>
                            <?php foreach ($guests as $guest): ?>
                            <option value="<?php echo $guest['id']; ?>">
                                <?php echo escape($guest['first_name'] . ' ' . $guest['last_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="order_type">Тип заказа:</label>
                        <select id="order_type" name="order_type" class="form-control" required>
                            <option value="bar">Бар (напитки)</option>
                            <option value="game">Игра (ставки)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="total_amount">Сумма заказа (руб.):</label>
                    <input type="number" id="total_amount" name="total_amount" class="form-control" required min="1" step="1" value="1000">
                </div>
                
                <div class="form-group">
                    <label for="status">Статус:</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending">В ожидании</option>
                        <option value="completed">Завершен</option>
                        <option value="cancelled">Отменен</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Примечания:</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Сохранить заказ
                    </button>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.order-type {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.order-type-bar {
    background: rgba(0, 128, 0, 0.2);
    color: #90EE90;
}

.order-type-game {
    background: rgba(199, 21, 133, 0.2);
    color: var(--secondary);
}

.status-pending {
    background: rgba(255, 165, 0, 0.2);
    color: #FFD700;
}

.status-completed {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}

.status-cancelled {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
}

.mt-4 {
    margin-top: 30px;
}
</style>

<?php require_once 'footer.php'; ?>