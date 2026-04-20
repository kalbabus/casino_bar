<?php
// order_success.php - СТРАНИЦА УСПЕШНОГО ЗАКАЗА
require_once 'config.php';
require_once 'header.php';

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    header('Location: menu_guest.php');
    exit;
}

$order = null;
$items = [];

try {
    $stmt = $pdo->prepare("
        SELECT o.*, g.first_name, g.last_name, g.phone 
        FROM orders o 
        LEFT JOIN guests g ON o.guest_id = g.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT od.*, mi.name 
        FROM order_details od 
        JOIN menu_items mi ON od.menu_item_id = mi.id 
        WHERE od.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<style>
.success-icon {
    font-size: 5rem;
    color: #28a745;
    margin-bottom: 20px;
}
.order-summary {
    background: rgba(26, 26, 26, 0.8);
    border-radius: 15px;
    padding: 30px;
    margin-top: 20px;
}
</style>

<div class="container" style="text-align: center;">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    <h1>Заказ успешно оформлен!</h1>
    <p style="font-size: 1.2rem;">Номер вашего заказа: <strong style="color: var(--accent);">#<?php echo $order_id; ?></strong></p>
    
    <div class="order-summary">
        <h3><i class="fas fa-receipt"></i> Детали заказа</h3>
        <table style="width: 100%; margin: 20px 0;">
            <thead>
                <tr><th>Блюдо</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 0); ?> ₽</td>
                    <td><?php echo number_format($item['unit_price'] * $item['quantity'], 0); ?> ₽</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top: 1px solid var(--accent);">
                    <td colspan="3"><strong>Итого:</strong></td>
                    <td><strong style="color: var(--accent);"><?php echo number_format($order['total_amount'], 0); ?> ₽</strong></td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 30px;">
            <a href="menu_guest.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Продолжить покупки
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> На главную
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>