<?php
// employees.php - БЕЗ ТЕЛЕФОНА И ДАТЫ НАЙМА
require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

// Обработка добавления/редактирования
$message = '';
$error = '';

// Обработка POST запроса (добавление/редактирование)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_employee'])) {
    $id = $_POST['id'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($position)) {
        $error = 'Имя, фамилия и должность обязательны для заполнения';
    } else {
        try {
            if (empty($id)) {
                $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, position) VALUES (?, ?, ?)");
                $stmt->execute([$first_name, $last_name, $position]);
                $message = 'Сотрудник успешно добавлен!';
            } else {
                $stmt = $pdo->prepare("UPDATE employees SET first_name=?, last_name=?, position=? WHERE id=?");
                $stmt->execute([$first_name, $last_name, $position, $id]);
                $message = 'Данные сотрудника обновлены!';
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
        $check = $pdo->prepare("SELECT COUNT(*) FROM shifts WHERE employee_id = ?");
        $check->execute([$id]);
        $shifts_count = $check->fetchColumn();
        
        if ($shifts_count > 0) {
            $error = 'Нельзя удалить сотрудника, у которого есть смены. Сначала удалите все смены сотрудника.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Сотрудник удален';
        }
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
}

// Получаем данные для редактирования
$edit_employee = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $edit_employee = $stmt->fetch();
}

// Получаем список сотрудников - выбираем только нужные колонки
$employees = [];
try {
    $stmt = $pdo->query("SELECT id, first_name, last_name, position FROM employees ORDER BY id DESC");
    $employees = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}
?>

<div class="container">
    <h1><i class="fas fa-users"></i> Управление сотрудниками</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Список сотрудников (<?php echo count($employees); ?>)</h3>
            <a href="?add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Добавить сотрудника
            </a>
        </div>
        
        <div class="card-body">
            <?php if (count($employees) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Должность</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['first_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td>
                                <span class="position-badge">
                                    <?php echo htmlspecialchars($row['position']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить сотрудника <?php echo addslashes($row['first_name'] . ' ' . $row['last_name']); ?>?')">
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
                <i class="fas fa-users" style="font-size: 4rem; opacity: 0.3;"></i>
                <p>Нет данных о сотрудниках</p>
                <a href="?add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Добавить первого сотрудника
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Форма добавления/редактирования (без телефона и даты) -->
    <?php if (isset($_GET['add']) || isset($_GET['edit'])): 
        $is_edit = isset($_GET['edit']);
        $employee = $edit_employee;
    ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>
                <i class="fas <?php echo $is_edit ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                <?php echo $is_edit ? 'Редактирование сотрудника' : 'Добавление нового сотрудника'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="employees.php" class="employee-form">
                <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($employee['id']); ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="first_name">Имя:</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-control" 
                               value="<?php echo $is_edit ? htmlspecialchars($employee['first_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="last_name">Фамилия:</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-control" 
                               value="<?php echo $is_edit ? htmlspecialchars($employee['last_name']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="position">Должность:</label>
                    <select id="position" name="position" class="form-control" required>
                        <option value="">Выберите должность</option>
                        <option value="Администратор" <?php echo ($is_edit && $employee['position'] == 'Администратор') ? 'selected' : ''; ?>>Администратор</option>
                        <option value="Бармен" <?php echo ($is_edit && $employee['position'] == 'Бармен') ? 'selected' : ''; ?>>Бармен</option>
                        <option value="Крупье" <?php echo ($is_edit && $employee['position'] == 'Крупье') ? 'selected' : ''; ?>>Крупье</option>
                        <option value="Официант" <?php echo ($is_edit && $employee['position'] == 'Официант') ? 'selected' : ''; ?>>Официант</option>
                        <option value="Охрана" <?php echo ($is_edit && $employee['position'] == 'Охрана') ? 'selected' : ''; ?>>Охрана</option>
                        <option value="Менеджер" <?php echo ($is_edit && $employee['position'] == 'Менеджер') ? 'selected' : ''; ?>>Менеджер</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_employee" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Сохранить изменения' : 'Добавить сотрудника'; ?>
                    </button>
                    <a href="employees.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.employee-form .form-group {
    margin-bottom: 20px;
}

.employee-form label {
    color: var(--accent);
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.employee-form .form-control {
    width: 100%;
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 8px;
    color: white;
}

.employee-form .form-control:focus {
    outline: none;
    border-color: var(--secondary);
}

.position-badge {
    display: inline-block;
    padding: 5px 12px;
    background: rgba(212, 175, 55, 0.2);
    color: var(--accent);
    border-radius: 20px;
    font-size: 0.9rem;
}

.mt-4 {
    margin-top: 30px;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-table th {
    color: var(--accent);
    font-weight: 600;
}
</style>

<?php require_once 'footer.php'; ?>