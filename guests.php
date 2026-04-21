<?php
// guests.php - ИСПРАВЛЕННАЯ ВЕРСИЯ (БЕЗ last_visit)
require_once 'config.php';
require_once 'header.php';

// Обработка POST запроса (добавление/редактирование)
$message = '';
$error = '';

// Обработка добавления/редактирования гостя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_guest'])) {
    $id = $_POST['id'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    $document_number = trim($_POST['document_number'] ?? '');
    $visits_count = (int)($_POST['visits_count'] ?? 0);
    $total_spent = (float)($_POST['total_spent'] ?? 0);
    
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        $error = 'Имя, фамилия и телефон обязательны для заполнения';
    } else {
        try {
            if (empty($id)) {
                // Добавление нового гостя
                $stmt = $pdo->prepare("
                    INSERT INTO guests (first_name, last_name, phone, birth_date, document_number, visits_count, total_spent, registration_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$first_name, $last_name, $phone, $birth_date, $document_number, $visits_count, $total_spent]);
                $message = 'Гость успешно добавлен!';
            } else {
                // Обновление существующего
                $stmt = $pdo->prepare("
                    UPDATE guests 
                    SET first_name=?, last_name=?, phone=?, birth_date=?, document_number=?, visits_count=?, total_spent=? 
                    WHERE id=?
                ");
                $stmt->execute([$first_name, $last_name, $phone, $birth_date, $document_number, $visits_count, $total_spent, $id]);
                $message = 'Данные гостя обновлены!';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

// Обработка удаления
if (isset($_GET['delete']) && !isGuest()) {
    $id = (int)$_GET['delete'];
    try {
        // Проверяем, есть ли у гостя заказы
        $check = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE guest_id = ?");
        $check->execute([$id]);
        $orders_count = $check->fetchColumn();
        
        if ($orders_count > 0) {
            $error = 'Нельзя удалить гостя, у которого есть заказы. Сначала удалите все заказы гостя.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM guests WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Гость удален';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
}

// Получаем данные для редактирования
$edit_guest = null;
if (isset($_GET['edit']) && !isGuest()) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ?");
    $stmt->execute([$id]);
    $edit_guest = $stmt->fetch();
}

// Получаем список гостей
$guests = [];
try {
    $stmt = $pdo->query("SELECT * FROM guests ORDER BY total_spent DESC");
    $guests = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}
?>

<div class="container">
    <h1><i class="fas fa-user-tie"></i> Гости казино</h1>
    
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
            <h3><i class="fas fa-list"></i> Список гостей (<?php echo count($guests); ?>)</h3>
            <?php if (!isGuest()): ?>
            <a href="?add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить гостя
            </a>
            <?php endif; ?>
        </div>
        
        <div class="card-body">
            <?php if (count($guests) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Телефон</th>
                            <th>Дата рождения</th>
                            <th>Посещений</th>
                            <th>Потрачено</th>
                            <?php if (!isGuest()): ?>
                            <th>Действия</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $row): ?>
                        <tr>
                            <td><?php echo escape($row['id']); ?></td>
                            <td><strong><?php echo escape($row['first_name']); ?></strong></td>
                            <td><?php echo escape($row['last_name']); ?></td>
                            <td><?php echo escape($row['phone']); ?></td>
                            <td><?php echo $row['birth_date'] ? date('d.m.Y', strtotime($row['birth_date'])) : '—'; ?></td>
                            <td class="text-center"><?php echo escape($row['visits_count']); ?></td>
                            <td class="amount-cell"><?php echo number_format($row['total_spent'], 0, '.', ' '); ?> ₽</td>
                            <?php if (!isGuest()): ?>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить гостя <?php echo addslashes($row['first_name'] . ' ' . $row['last_name']); ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-user-tie" style="font-size: 4rem; opacity: 0.3;"></i>
                <p>Нет данных о гостях</p>
                <?php if (!isGuest()): ?>
                <a href="?add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить первого гостя
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Форма добавления/редактирования -->
    <?php if ((isset($_GET['add']) || isset($_GET['edit'])) && !isGuest()): 
        $is_edit = isset($_GET['edit']);
        $guest = $edit_guest;
    ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>
                <i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                <?php echo $is_edit ? 'Редактирование гостя' : 'Добавление нового гостя'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="guests.php" class="guest-form">
                <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $guest['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="first_name">Имя:</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-control" 
                               value="<?php echo $is_edit ? escape($guest['first_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="last_name">Фамилия:</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-control" 
                               value="<?php echo $is_edit ? escape($guest['last_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="phone">Телефон:</label>
                        <input type="text" 
                               id="phone" 
                               name="phone" 
                               class="form-control" 
                               value="<?php echo $is_edit ? escape($guest['phone']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="birth_date">Дата рождения:</label>
                        <input type="date" 
                               id="birth_date" 
                               name="birth_date" 
                               class="form-control" 
                               value="<?php echo $is_edit && $guest['birth_date'] ? $guest['birth_date'] : ''; ?>">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="document_number">Номер документа:</label>
                        <input type="text" 
                               id="document_number" 
                               name="document_number" 
                               class="form-control" 
                               value="<?php echo $is_edit ? escape($guest['document_number']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="visits_count">Количество посещений:</label>
                        <input type="number" 
                               id="visits_count" 
                               name="visits_count" 
                               class="form-control" 
                               value="<?php echo $is_edit ? $guest['visits_count'] : '0'; ?>" 
                               min="0"
                               required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="total_spent">Всего потрачено (₽):</label>
                        <input type="number" 
                               id="total_spent" 
                               name="total_spent" 
                               class="form-control" 
                               value="<?php echo $is_edit ? $guest['total_spent'] : '0'; ?>" 
                               min="0"
                               step="100"
                               required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_guest" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Сохранить изменения' : 'Добавить гостя'; ?>
                    </button>
                    <a href="guests.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.text-center {
    text-align: center;
}

.mt-4 {
    margin-top: 30px;
}

.guest-form .form-group {
    margin-bottom: 20px;
}

.guest-form label {
    color: var(--accent);
    margin-bottom: 8px;
    display: block;
}

.guest-form .form-control {
    width: 100%;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 5px;
    color: white;
}

.guest-form .form-control:focus {
    outline: none;
    border-color: var(--secondary);
}
</style>

<?php require_once 'footer.php'; ?>