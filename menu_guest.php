<?php
// menu_guest.php - С ПРОВЕРКОЙ СУЩЕСТВУЮЩЕГО ГОСТЯ
require_once 'config.php';
require_once 'header.php';

$message = '';
$error = '';
$cart = $_SESSION['cart'] ?? [];

// Добавление товара в корзину
if (isset($_GET['add_to_cart'])) {
    $item_id = (int)$_GET['add_to_cart'];
    $quantity = (int)($_GET['quantity'] ?? 1);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if ($item) {
            if (isset($cart[$item_id])) {
                $cart[$item_id]['quantity'] += $quantity;
            } else {
                $cart[$item_id] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $quantity
                ];
            }
            $_SESSION['cart'] = $cart;
            $message = "{$item['name']} добавлен в корзину!";
        } else {
            $error = 'Товар не найден или недоступен';
        }
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
    header('Location: menu_guest.php');
    exit;
}

// Удаление из корзины
if (isset($_GET['remove_from_cart'])) {
    $item_id = (int)$_GET['remove_from_cart'];
    if (isset($cart[$item_id])) {
        unset($cart[$item_id]);
        $_SESSION['cart'] = $cart;
        $message = 'Товар удален из корзины';
    }
    header('Location: menu_guest.php');
    exit;
}

// Обновление количества
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $item_id => $quantity) {
        $item_id = (int)$item_id;
        $quantity = (int)$quantity;
        if ($quantity > 0 && isset($cart[$item_id])) {
            $cart[$item_id]['quantity'] = $quantity;
        } elseif ($quantity <= 0 && isset($cart[$item_id])) {
            unset($cart[$item_id]);
        }
    }
    $_SESSION['cart'] = $cart;
    header('Location: menu_guest.php#cart');
    exit;
}

// Оформление заказа - С ПРОВЕРКОЙ СУЩЕСТВУЮЩЕГО ГОСТЯ
if (isset($_POST['checkout']) && !empty($cart)) {
    $guest_name = trim($_POST['guest_name'] ?? '');
    $guest_phone = trim($_POST['guest_phone'] ?? '');
    
    if (empty($guest_name) || empty($guest_phone)) {
        $error = 'Пожалуйста, укажите ваше имя и телефон для заказа';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Ищем гостя по имени ИЛИ по телефону
            $stmt = $pdo->prepare("
                SELECT id, first_name, last_name, phone, total_spent 
                FROM guests 
                WHERE first_name = ? OR last_name = ? OR phone = ?
            ");
            $stmt->execute([$guest_name, $guest_name, $guest_phone]);
            $guest = $stmt->fetch();
            
            // Если гость НЕ найден - выдаем ошибку
            if (!$guest) {
                $error = 'Гость не найден в системе! Пожалуйста, зарегистрируйтесь или проверьте правильность имени/телефона.';
                $pdo->rollBack();
            } else {
                // Гость найден - используем его данные
                $guest_id = $guest['id'];
                $guest_full_name = $guest['first_name'] . ' ' . $guest['last_name'];
                
                // Считаем общую сумму
                $total = 0;
                foreach ($cart as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
                
                // Создаем заказ
                $stmt = $pdo->prepare("
                    INSERT INTO orders (guest_id, employee_id, order_type, total_amount, status, order_time) 
                    VALUES (?, 1, 'bar', ?, 'pending', NOW())
                ");
                $stmt->execute([$guest_id, $total]);
                $order_id = $pdo->lastInsertId();
                
                // Добавляем детали заказа
                foreach ($cart as $item) {
                    $stmt = $pdo->prepare("
                        INSERT INTO order_details (order_id, menu_item_id, quantity, unit_price) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                }
                
                // Обновляем сумму потраченного у гостя (ДОБАВЛЯЕМ к существующей)
                $stmt = $pdo->prepare("UPDATE guests SET total_spent = total_spent + ?, visits_count = visits_count + 1 WHERE id = ?");
                $stmt->execute([$total, $guest_id]);
                
                $pdo->commit();
                
                // Очищаем корзину
                $_SESSION['cart'] = [];
                
                // Сохраняем сообщение об успехе в сессию
                $_SESSION['order_success'] = "Заказ #{$order_id} оформлен! Сумма {$total} ₽ добавлена к счету гостя {$guest_full_name}.";
                
                header("Location: order_success.php?order_id={$order_id}");
                exit;
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка оформления заказа: ' . $e->getMessage();
        }
    }
}

// Получаем всех существующих гостей для подсказок
$existing_guests = [];
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, phone FROM guests ORDER BY first_name LIMIT 50");
    $existing_guests = $stmt->fetchAll();
} catch (Exception $e) {
    // Игнорируем ошибку
}

// Получаем все доступные товары из menu_items
$menu_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY category, name");
    $menu_items = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Ошибка загрузки меню: ' . $e->getMessage();
}

// Группируем товары по категориям
$items_by_category = [];
foreach ($menu_items as $item) {
    $category = $item['category'];
    if (!isset($items_by_category[$category])) {
        $items_by_category[$category] = [];
    }
    $items_by_category[$category][] = $item;
}

// Названия категорий на русском
$categoryNames = [
    'alcohol' => '🍷 Алкогольные напитки',
    'beer' => '🍺 Пиво',
    'wine' => '🍷 Вино',
    'spirits' => '🥃 Крепкий алкоголь',
    'cocktails' => '🍸 Коктейли',
    'soft' => '🥤 Безалкогольные напитки',
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
    'garnish' => '🍚 Гарниры'
];
?>

<style>
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.menu-item-card {
    background: rgba(26, 26, 26, 0.8);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(212, 175, 55, 0.3);
    transition: all 0.3s;
    position: relative;
}

.menu-item-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
}

.menu-item-price {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--accent);
}

.menu-item-price small {
    font-size: 0.8rem;
    color: var(--secondary-light);
}

.add-to-cart-btn {
    background: linear-gradient(45deg, var(--secondary), var(--primary));
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s;
}

.add-to-cart-btn:hover {
    transform: scale(1.05);
}

.category-section {
    margin-bottom: 40px;
}

.category-title {
    color: var(--accent);
    border-bottom: 2px solid var(--accent);
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.cart-sidebar {
    position: sticky;
    top: 20px;
    background: rgba(26, 26, 26, 0.95);
    border-radius: 15px;
    padding: 20px;
    border: 1px solid rgba(212, 175, 55, 0.3);
    max-height: 80vh;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-weight: bold;
}

.cart-item-price {
    color: var(--accent);
    font-size: 0.9rem;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-input {
    width: 50px;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 5px;
    color: white;
    padding: 5px;
}

.cart-total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid var(--accent);
    font-size: 1.2rem;
    font-weight: bold;
}

.checkout-form {
    margin-top: 20px;
}

.checkout-form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 8px;
    color: white;
}

.empty-cart {
    text-align: center;
    padding: 30px;
    color: var(--secondary-light);
}

.category-filter {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.filter-btn {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    padding: 8px 20px;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s;
    color: white;
}

.filter-btn.active {
    background: var(--accent);
    color: #1a1a1a;
}

.filter-btn:hover {
    background: rgba(212, 175, 55, 0.3);
}

.two-column-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.guest-suggestions {
    font-size: 0.8rem;
    color: var(--secondary-light);
    margin-top: -5px;
    margin-bottom: 10px;
    padding: 5px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 5px;
}

.guest-suggestions span {
    display: inline-block;
    background: rgba(212, 175, 55, 0.2);
    padding: 2px 8px;
    border-radius: 15px;
    margin: 2px;
    font-size: 0.75rem;
}

.info-box-guest {
    background: rgba(23, 162, 184, 0.1);
    border: 1px solid #17a2b8;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 15px;
    font-size: 0.85rem;
}

.info-box-guest i {
    color: #17a2b8;
}

@media (max-width: 768px) {
    .two-column-layout {
        grid-template-columns: 1fr;
    }
    .cart-sidebar {
        position: static;
        order: -1;
    }
}
</style>

<div class="container">
    <h1><i class="fas fa-utensils"></i> Заказ еды и напитков</h1>
    <p style="color: var(--secondary-light); margin-bottom: 30px;">Выберите блюда из меню, добавьте в корзину и оформите заказ</p>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="two-column-layout">
        <!-- Левая колонка - меню -->
        <div>
            <div class="category-filter">
                <button class="filter-btn active" data-category="all">Все категории</button>
                <?php foreach (array_keys($items_by_category) as $cat): ?>
                    <button class="filter-btn" data-category="<?php echo $cat; ?>">
                        <?php echo $categoryNames[$cat] ?? ucfirst($cat); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            
            <?php foreach ($items_by_category as $category => $items): ?>
                <div class="category-section" data-category="<?php echo $category; ?>">
                    <h2 class="category-title"><?php echo $categoryNames[$category] ?? ucfirst($category); ?></h2>
                    <div class="menu-grid">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-item-card">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p style="color: var(--secondary-light); font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($item['description'] ?? 'Без описания'); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                    <div class="menu-item-price">
                                        <?php echo number_format($item['price'], 0); ?> ₽
                                        <small>за шт</small>
                                    </div>
                                    <form method="GET" action="menu_guest.php" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="add_to_cart" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="99" style="width: 60px; padding: 5px; background: rgba(255,255,255,0.1); border: 1px solid var(--accent); border-radius: 5px; color: white; text-align: center;">
                                        <button type="submit" class="add-to-cart-btn">
                                            <i class="fas fa-cart-plus"></i> В корзину
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($menu_items)): ?>
                <div class="no-data">
                    <i class="fas fa-utensils" style="font-size: 4rem; opacity: 0.3;"></i>
                    <p>Меню пусто. Добавьте товары в админ-панели.</p>
                    <?php if (!isGuest()): ?>
                        <a href="menu_items.php" class="btn btn-primary">Добавить товары</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Правая колонка - корзина -->
        <div class="cart-sidebar" id="cart">
            <h3><i class="fas fa-shopping-cart"></i> Ваша корзина</h3>
            
            <?php if (!empty($cart)): ?>
                <form method="POST">
                    <?php foreach ($cart as $item_id => $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-price"><?php echo number_format($item['price'], 0); ?> ₽</div>
                            </div>
                            <div class="cart-item-quantity">
                                <input type="number" name="quantity[<?php echo $item_id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="99" class="quantity-input" onchange="this.form.submit()">
                                <a href="?remove_from_cart=<?php echo $item_id; ?>" style="color: #dc3545;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="update_cart" value="1">
                </form>
                
                <div class="cart-total">
                    Итого: 
                    <?php 
                        $total = 0;
                        foreach ($cart as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                        echo number_format($total, 0); 
                    ?> ₽
                </div>
                
                <div class="info-box-guest">
                    <i class="fas fa-info-circle"></i> <strong>Внимание!</strong><br>
                    Заказ может оформить только зарегистрированный гость.<br>
                    Введите ваше <strong>Имя</strong> или <strong>Телефон</strong> из системы.
                </div>
                
                <form method="POST" class="checkout-form" onsubmit="return validateCheckout()">
                    <input type="hidden" name="checkout" value="1">
                    <input type="text" name="guest_name" id="guest_name" placeholder="Ваше имя (как в системе)" required autocomplete="off" oninput="showGuestSuggestions()">
                    <input type="tel" name="guest_phone" id="guest_phone" placeholder="Ваш телефон (как в системе)" required autocomplete="off" oninput="showGuestSuggestions()">
                    
                    <div id="guest_suggestions" class="guest-suggestions"></div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-check-circle"></i> Оформить заказ
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p>Корзина пуста</p>
                    <p style="font-size: 0.9rem;">Добавьте блюда из меню</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Данные существующих гостей
const existingGuests = <?php echo json_encode($existing_guests); ?>;

function showGuestSuggestions() {
    const nameInput = document.getElementById('guest_name').value.toLowerCase();
    const phoneInput = document.getElementById('guest_phone').value.toLowerCase();
    const suggestionsDiv = document.getElementById('guest_suggestions');
    
    let matches = existingGuests.filter(guest => {
        const fullName = (guest.first_name + ' ' + guest.last_name).toLowerCase();
        const firstName = guest.first_name.toLowerCase();
        const lastName = guest.last_name.toLowerCase();
        const phone = guest.phone.toLowerCase();
        
        if (nameInput && (fullName.includes(nameInput) || firstName.includes(nameInput) || lastName.includes(nameInput))) {
            return true;
        }
        if (phoneInput && phone.includes(phoneInput)) {
            return true;
        }
        return false;
    });
    
    if (matches.length > 0 && (nameInput.length > 1 || phoneInput.length > 3)) {
        suggestionsDiv.innerHTML = '<i class="fas fa-users"></i> Найдены гости: ' + 
            matches.slice(0, 5).map(g => 
                `<span onclick="fillGuest('${g.first_name} ${g.last_name}', '${g.phone}')">${g.first_name} ${g.last_name} (${g.phone})</span>`
            ).join('');
        suggestionsDiv.style.display = 'block';
    } else if (nameInput.length > 2 || phoneInput.length > 5) {
        suggestionsDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Гость не найден. Проверьте имя или телефон.';
        suggestionsDiv.style.color = '#ffc107';
    } else {
        suggestionsDiv.innerHTML = '';
    }
}

function fillGuest(name, phone) {
    document.getElementById('guest_name').value = name;
    document.getElementById('guest_phone').value = phone;
    document.getElementById('guest_suggestions').innerHTML = '';
}

function validateCheckout() {
    const name = document.getElementById('guest_name').value.trim();
    const phone = document.getElementById('guest_phone').value.trim();
    
    if (!name || !phone) {
        alert('Пожалуйста, укажите ваше имя и телефон');
        return false;
    }
    
    // Проверяем, существует ли гость в системе
    let guestExists = false;
    for (let guest of existingGuests) {
        const fullName = (guest.first_name + ' ' + guest.last_name).toLowerCase();
        const firstName = guest.first_name.toLowerCase();
        const lastName = guest.last_name.toLowerCase();
        const guestPhone = guest.phone.toLowerCase();
        
        if ((fullName === name.toLowerCase() || firstName === name.toLowerCase() || lastName === name.toLowerCase()) &&
            guestPhone === phone.toLowerCase()) {
            guestExists = true;
            break;
        }
    }
    
    if (!guestExists) {
        alert('Гость не найден в системе! Пожалуйста, зарегистрируйтесь или проверьте правильность имени и телефона.');
        return false;
    }
    
    return confirm('Подтвердите оформление заказа? Сумма будет добавлена к вашему счету.');
}

// Фильтрация категорий
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;
        
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (category === 'all') {
            document.querySelectorAll('.category-section').forEach(section => {
                section.style.display = 'block';
            });
        } else {
            document.querySelectorAll('.category-section').forEach(section => {
                if (section.dataset.category === category) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        }
    });
});

// Автообновление количества
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>

<?php require_once 'footer.php'; ?>