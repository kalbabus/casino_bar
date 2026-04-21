<?php
// save_menu_item.php - ОБНОВЛЕННАЯ ВЕРСИЯ (редирект на menu_items.php)
require_once 'config.php';

if (isGuest()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = (float)($_POST['price'] ?? 0);
    $is_available = (int)($_POST['is_available'] ?? 1);
    
    try {
        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, description, price, is_available) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $description, $price, $is_available]);
        } else {
            $stmt = $pdo->prepare("UPDATE menu_items SET name=?, category=?, description=?, price=?, is_available=? WHERE id=?");
            $stmt->execute([$name, $category, $description, $price, $is_available, $id]);
        }
        
        header('Location: menu_items.php?success=1');
        exit;
    } catch (Exception $e) {
        header('Location: menu_items.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

header('Location: menu_items.php');
exit;
?>