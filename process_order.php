<?php
// process_order.php - ИСПРАВЛЕННАЯ ВЕРСИЯ
require_once 'config.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_id = !empty($_POST['guest_id']) ? (int)$_POST['guest_id'] : null;
    $order_type = $_POST['order_type'] ?? 'bar';
    $total_amount = (float)($_POST['total_amount'] ?? 0);
    $status = $_POST['status'] ?? 'pending';
    $notes = $_POST['notes'] ?? '';
    
    // Валидация
    if ($total_amount <= 0) {
        header('Location: orders.php?add=1&error=' . urlencode('Сумма заказа должна быть больше 0'));
        exit;
    }
    
    if (!in_array($order_type, ['bar', 'game'])) {
        $order_type = 'bar';
    }
    
    if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
        $status = 'pending';
    }
    
    try {
        // Начинаем транзакцию
        $pdo->beginTransaction();
        
        // Вставляем заказ
        $stmt = $pdo->prepare("
            INSERT INTO orders (guest_id, order_type, total_amount, status, notes, order_time) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$guest_id, $order_type, $total_amount, $status, $notes]);
        $order_id = $pdo->lastInsertId();
        
        // Обновляем статистику гостя, если это зарегистрированный гость
        if ($guest_id) {
            // Проверяем, есть ли у гостя поле last_visit
            $check = $pdo->query("SHOW COLUMNS FROM guests LIKE 'last_visit'");
            if ($check->rowCount() > 0) {
                $update_guest = $pdo->prepare("
                    UPDATE guests 
                    SET visits_count = visits_count + 1, 
                        total_spent = total_spent + ?,
                        last_visit = NOW()
                    WHERE id = ?
                ");
            } else {
                $update_guest = $pdo->prepare("
                    UPDATE guests 
                    SET visits_count = visits_count + 1, 
                        total_spent = total_spent + ?
                    WHERE id = ?
                ");
            }
            $update_guest->execute([$total_amount, $guest_id]);
        }
        
        // Подтверждаем транзакцию
        $pdo->commit();
        
        // Перенаправление с сообщением об успехе
        header('Location: orders.php?success=1&id=' . $order_id);
        exit;
        
    } catch (PDOException $e) {
        // Откатываем транзакцию в случае ошибки
        $pdo->rollBack();
        header('Location: orders.php?add=1&error=' . urlencode('Ошибка базы данных: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: orders.php');
    exit;
}
?>