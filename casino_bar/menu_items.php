<?php
// menu_items.php - ЛЮБАЯ ЦЕНА (без ограничений)
require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// ========== ДОБАВЛЕНИЕ НОВОЙ ПОЗИЦИИ ==========
if (isset($_POST['add_item'])) {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name)) {
        $error = 'Название позиции обязательно!';
    } elseif (empty($category)) {
        $error = 'Выберите категорию!';
    } elseif ($price <= 0) {
        $error = 'Цена должна быть больше 0!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, price, stock_quantity, is_available) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $stock_quantity, $is_available]);
            $message = "Позиция '{$name}' успешно добавлена!";
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// ========== РЕДАКТИРОВАНИЕ ПОЗИЦИИ ==========
if (isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name)) {
        $error = 'Название позиции обязательно!';
    } elseif ($price <= 0) {
        $error = 'Цена должна быть больше 0!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE menu_items SET name=?, category=?, price=?, stock_quantity=?, is_available=? WHERE id=?");
            $stmt->execute([$name, $category, $price, $stock_quantity, $is_available, $id]);
            $message = "Позиция '{$name}' успешно обновлена!";
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// ========== УДАЛЕНИЕ ПОЗИЦИИ ==========
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $check = $pdo->prepare("SELECT COUNT(*) FROM order_details WHERE menu_item_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            $error = 'Нельзя удалить позицию, которая есть в заказах!';
        } else {
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Позиция удалена';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка удаления: ' . $e->getMessage();
    }
}

// ========== ПОЛУЧАЕМ ДАННЫЕ ДЛЯ РЕДАКТИРОВАНИЯ ==========
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// ========== ПОЛУЧАЕМ ВСЕ ПОЗИЦИИ МЕНЮ ==========
$menuItems = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
    $menuItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка загрузки: ' . $e->getMessage();
}

// Названия категорий
$categories = [
    'alcohol' => '🍷 Алкогольные напитки',
    'beer' => '🍺 Пиво',
    'wine' => '🍷 Вино',
    'spirits' => '🥃 Крепкий алкоголь',
    'cocktails' => '🍸 Коктейли',
    'soft' => '🥤 Безалкогольные',
    'juices' => '🧃 Соки',
    'water' => '💧 Вода',
    'coffee' => '☕ Кофе',
    'tea' => '🍵 Чай',
    'appetizers' => '🥗 Закуски',
    'hot_dishes' => '🍲 Горячие блюда',
    'desserts' => '🍰 Десерты',
    'pizza' => '🍕 Пицца',
    'burgers' => '🍔 Бургеры'
];
?>

<div class="container">
    <h1><i class="fas fa-utensils"></i> Управление меню</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-success" style="background: rgba(40,167,69,0.2); border:1px solid #28a745; padding:12px; border-radius:8px; margin-bottom:20px;">
            ✅ <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: rgba(220,53,69,0.2); border:1px solid #dc3545; padding:12px; border-radius:8px; margin-bottom:20px;">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-bottom: 20px;">
        <a href="?add=1" class="btn btn-primary" style="background: linear-gradient(45deg, #D4AF37, #B8860B); padding: 10px 20px; border-radius: 8px; color: white; text-decoration: none;">
            <i class="fas fa-plus"></i> Добавить позицию в меню
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Все позиции меню (<?php echo count($menuItems); ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (count($menuItems) > 0): ?>
            <table class="data-table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #D4AF37;">
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Название</th>
                        <th style="padding: 12px; text-align: left;">Категория</th>
                        <th style="padding: 12px; text-align: left;">Цена</th>
                        <th style="padding: 12px; text-align: left;">Остаток</th>
                        <th style="padding: 12px; text-align: left;">Доступно</th>
                        <th style="padding: 12px; text-align: left;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding: 12px;"><?php echo $item['id']; ?></td>
                        <td style="padding: 12px;"><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td style="padding: 12px;">
                            <span style="background: rgba(212,175,55,0.2); padding: 4px 12px; border-radius: 20px;">
                                <?php echo $categories[$item['category']] ?? $item['category']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #D4AF37; font-weight: bold;"><?php echo number_format($item['price'], 0); ?> ₽</td>
                        <td style="padding: 12px;"><?php echo $item['stock_quantity'] ?? 0; ?> шт.</td>
                        <td style="padding: 12px;">
                            <?php if ($item['is_available']): ?>
                                <span style="color: #28a745;">✅ Доступно</span>
                            <?php else: ?>
                                <span style="color: #dc3545;">❌ Недоступно</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px;">
                            <a href="?edit=<?php echo $item['id']; ?>" style="color: #ffc107; margin-right: 10px; text-decoration: none;">✏️</a>
                            <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Удалить?')" style="color: #dc3545; text-decoration: none;">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <i class="fas fa-utensils" style="font-size: 4rem; opacity: 0.3;"></i>
                <p style="margin-top: 20px;">Меню пусто. Добавьте первую позицию!</p>
                <a href="?add=1" class="btn btn-primary">➕ Добавить позицию</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ФОРМА ДОБАВЛЕНИЯ/РЕДАКТИРОВАНИЯ -->
    <?php if (isset($_GET['add']) || isset($_GET['edit'])): 
        $is_edit = isset($_GET['edit']);
        $item = $edit_item;
    ?>
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h3><i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> 
                <?php echo $is_edit ? 'Редактирование позиции' : 'Добавление новой позиции'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="menu_items.php" style="max-width: 500px;">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="edit_item" value="1">
                <?php else: ?>
                    <input type="hidden" name="add_item" value="1">
                <?php endif; ?>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #D4AF37; margin-bottom: 8px;">Название *</label>
                    <input type="text" name="name" required 
                           value="<?php echo $is_edit ? htmlspecialchars($item['name']) : ''; ?>"
                           style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); border: 1px solid #D4AF37; border-radius: 8px; color: white;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #D4AF37; margin-bottom: 8px;">Категория *</label>
                    <select name="category" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); border: 1px solid #D4AF37; border-radius: 8px; color: white;">
                        <option value="">-- Выберите категорию --</option>
                        <?php foreach ($categories as $key => $name): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($is_edit && $item['category'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #D4AF37; margin-bottom: 8px;">Цена (₽) *</label>
                    <input type="number" name="price" required min="1" step="1" 
                           value="<?php echo $is_edit ? $item['price'] : '100'; ?>"
                           style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); border: 1px solid #D4AF37; border-radius: 8px; color: white;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #D4AF37; margin-bottom: 8px;">Количество на складе</label>
                    <input type="number" name="stock_quantity" min="0" step="1"
                           value="<?php echo $is_edit ? ($item['stock_quantity'] ?? 0) : '0'; ?>"
                           style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); border: 1px solid #D4AF37; border-radius: 8px; color: white;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #D4AF37; margin-bottom: 8px;">
                        <input type="checkbox" name="is_available" value="1" <?php echo (!$is_edit || $item['is_available']) ? 'checked' : ''; ?> style="margin-right: 8px;">
                        Доступно для заказа
                    </label>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn btn-success" style="background: linear-gradient(45deg, #28a745, #20c997); padding: 12px 24px; border: none; border-radius: 8px; color: white; cursor: pointer;">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Сохранить' : 'Добавить'; ?>
                    </button>
                    <a href="menu_items.php" class="btn btn-secondary" style="background: rgba(108,117,125,0.3); padding: 12px 24px; border-radius: 8px; color: white; text-decoration: none;">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>