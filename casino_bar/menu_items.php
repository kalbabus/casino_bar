<?php
// menu_items.php - ИСПРАВЛЕННАЯ ВЕРСИЯ, РАБОТАЕТ С БД
require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

// Обработка POST запроса (сохранение)
$message = '';
$error = '';

// Обработка сохранения позиции меню
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menu_item'])) {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $is_available = (int)($_POST['is_available'] ?? 1);
    
    if (empty($name) || empty($category) || $price <= 0) {
        $error = 'Название, категория и цена обязательны для заполнения';
    } else {
        try {
            if (empty($id)) {
                // Добавление новой позиции
                $stmt = $pdo->prepare("INSERT INTO menu_items (name, category, description, price, is_available, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $category, $description, $price, $is_available]);
                $message = 'Позиция успешно добавлена в меню!';
            } else {
                // Обновление существующей
                $stmt = $pdo->prepare("UPDATE menu_items SET name=?, category=?, description=?, price=?, is_available=? WHERE id=?");
                $stmt->execute([$name, $category, $description, $price, $is_available, $id]);
                $message = 'Позиция меню обновлена!';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Проверяем, есть ли позиция в заказах
        $check = $pdo->prepare("SELECT COUNT(*) FROM order_details WHERE menu_item_id = ?");
        $check->execute([$id]);
        $orders_count = $check->fetchColumn();
        
        if ($orders_count > 0) {
            $error = 'Нельзя удалить позицию, которая есть в заказах. Сначала удалите все заказы с этой позицией.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Позиция удалена из меню';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
}

// Получаем данные для редактирования
$edit_item = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
}

// Получаем все позиции меню
$menuItems = [];
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_items ORDER BY category, name");
    $menuItems = $stmt->fetchAll();
    
    $catStmt = $pdo->query("SELECT DISTINCT category FROM menu_items ORDER BY category");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}

// Массив для названий категорий на русском
$categoryNames = [
    'alcohol' => '🍷 Алкоголь',
    'beer' => '🍺 Пиво',
    'wine' => '🍷 Вино',
    'spirits' => '🥃 Крепкий алкоголь',
    'cocktails' => '🍸 Коктейли',
    'soft' => '🧃 Безалкогольные',
    'juices' => '🧃 Соки',
    'water' => '💧 Вода',
    'soda' => '🥤 Газировка',
    'hot' => '☕ Горячие напитки',
    'coffee' => '☕ Кофе',
    'tea' => '🍵 Чай',
    'appetizers' => '🥗 Закуски',
    'hot_dishes' => '🍲 Горячие блюда',
    'desserts' => '🍰 Десерты',
    'snacks' => '🍿 Снеки',
    'salads' => '🥗 Салаты',
    'soups' => '🍜 Супы',
    'pasta' => '🍝 Паста',
    'pizza' => '🍕 Пицца',
    'burgers' => '🍔 Бургеры',
    'fish' => '🐟 Рыба',
    'meat' => '🥩 Мясо',
    'garnish' => '🍚 Гарниры',
    'breakfast' => '🍳 Завтраки'
];
?>

<div class="container">
    <h1><i class="fas fa-utensils"></i> Меню ресторана и бара</h1>
    
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
            <h3><i class="fas fa-list"></i> Все позиции меню (<?php echo count($menuItems); ?>)</h3>
            <a href="?add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить позицию
            </a>
        </div>
        
        <div class="card-body">
            <!-- Фильтры -->
            <div class="filters">
                <div class="filter-group">
                    <label>Категория:</label>
                    <select id="filterCategory" class="filter-select">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo $categoryNames[$cat] ?? ucfirst(str_replace('_', ' ', $cat)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Доступность:</label>
                    <select id="filterAvailability" class="filter-select">
                        <option value="">Все</option>
                        <option value="1">✅ Доступно</option>
                        <option value="0">❌ Недоступно</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Поиск:</label>
                    <input type="text" id="searchInput" class="filter-input" placeholder="Название...">
                </div>
            </div>
            
            <!-- Таблица меню -->
            <?php if (count($menuItems) > 0): ?>
            <div class="table-responsive">
                <table class="data-table" id="menuTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Описание</th>
                            <th>Цена</th>
                            <th>Доступность</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $item): 
                            $categoryDisplay = $categoryNames[$item['category']] ?? ucfirst(str_replace('_', ' ', $item['category']));
                        ?>
                        <tr data-category="<?php echo htmlspecialchars($item['category']); ?>" 
                            data-available="<?php echo $item['is_available']; ?>"
                            data-name="<?php echo htmlspecialchars(strtolower($item['name'])); ?>">
                            <td><?php echo $item['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td>
                                <span class="category-badge">
                                    <?php echo $categoryDisplay; ?>
                                </span>
                            </td>
                            <td class="description-cell">
                                <?php echo htmlspecialchars($item['description'] ?? 'Нет описания'); ?>
                            </td>
                            <td class="amount-cell"><?php echo number_format($item['price'], 0); ?> ₽</td>
                            <td>
                                <span class="availability-badge <?php echo $item['is_available'] ? 'availability-yes' : 'availability-no'; ?>">
                                    <?php echo $item['is_available'] ? '✅ Доступно' : '❌ Недоступно'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn-action btn-edit" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $item['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить позицию \'<?php echo addslashes($item['name']); ?>\'?')">
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
                <i class="fas fa-utensils" style="font-size: 4rem; opacity: 0.3;"></i>
                <p>Меню пусто</p>
                <a href="?add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить первую позицию
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Форма добавления/редактирования -->
    <?php if (isset($_GET['add']) || isset($_GET['edit'])): 
        $is_edit = isset($_GET['edit']);
        $item = $edit_item;
    ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>
                <i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                <?php echo $is_edit ? 'Редактирование позиции' : 'Добавление новой позиции'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="menu_items.php" class="menu-form">
                <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="itemName">Название:</label>
                        <input type="text" 
                               id="itemName" 
                               name="name" 
                               class="form-control" 
                               value="<?php echo $is_edit ? htmlspecialchars($item['name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="itemCategory">Категория:</label>
                        <select id="itemCategory" name="category" class="form-control" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categoryNames as $catKey => $catName): ?>
                            <option value="<?php echo $catKey; ?>" 
                                <?php echo ($is_edit && $item['category'] == $catKey) ? 'selected' : ''; ?>>
                                <?php echo $catName; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="itemDescription">Описание:</label>
                    <textarea id="itemDescription" 
                              name="description" 
                              class="form-control" 
                              rows="3"><?php echo $is_edit ? htmlspecialchars($item['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="itemPrice">Цена (₽):</label>
                        <input type="number" 
                               id="itemPrice" 
                               name="price" 
                               class="form-control" 
                               min="1" 
                               step="10" 
                               value="<?php echo $is_edit ? $item['price'] : '100'; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="itemAvailability">Доступность:</label>
                        <select id="itemAvailability" name="is_available" class="form-control">
                            <option value="1" <?php echo (!$is_edit || $item['is_available']) ? 'selected' : ''; ?>>✅ Доступно</option>
                            <option value="0" <?php echo ($is_edit && !$item['is_available']) ? 'selected' : ''; ?>>❌ Недоступно</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_menu_item" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Сохранить изменения' : 'Добавить позицию'; ?>
                    </button>
                    <a href="menu_items.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.filters {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: rgba(26, 26, 26, 0.6);
    border-radius: 10px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1 1 200px;
}

.filter-group label {
    color: var(--accent);
    white-space: nowrap;
}

.filter-select, .filter-input {
    width: 100%;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 5px;
    color: white;
}

.category-badge {
    display: inline-block;
    padding: 5px 12px;
    background: rgba(212, 175, 55, 0.2);
    color: var(--accent);
    border-radius: 20px;
    font-size: 0.85rem;
}

.availability-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
}

.availability-yes {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}

.availability-no {
    background: rgba(244, 67, 54, 0.2);
    color: #f44336;
}

.description-cell {
    max-width: 300px;
    color: var(--secondary-light);
    font-size: 0.9rem;
}

.mt-4 {
    margin-top: 30px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterCategory = document.getElementById('filterCategory');
    const filterAvailability = document.getElementById('filterAvailability');
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('#menuTable tbody tr');
    
    function applyFilters() {
        const categoryValue = filterCategory ? filterCategory.value : '';
        const availabilityValue = filterAvailability ? filterAvailability.value : '';
        const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
        
        tableRows.forEach(row => {
            const rowCategory = row.getAttribute('data-category');
            const rowAvailability = row.getAttribute('data-available');
            const rowName = row.getAttribute('data-name') || '';
            
            let showRow = true;
            
            if (categoryValue && rowCategory !== categoryValue) showRow = false;
            if (availabilityValue && rowAvailability !== availabilityValue) showRow = false;
            if (searchValue && !rowName.includes(searchValue)) showRow = false;
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    if (filterCategory) filterCategory.addEventListener('change', applyFilters);
    if (filterAvailability) filterAvailability.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
});
</script>

<?php require_once 'footer.php'; ?>