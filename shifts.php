<?php
// shifts.php - ПОЛНОСТЬЮ РАБОЧАЯ ВЕРСИЯ
require_once 'config.php';
require_once 'header.php';

// Проверка авторизации
if (isGuest()) {
    header('Location: login.php');
    exit;
}

// Обработка действий
$message = '';
$error = '';

// Открытие новой смены
if (isset($_GET['new'])) {
    // Получаем список сотрудников для выбора
    $employees = [];
    try {
        $stmt = $pdo->query("SELECT id, first_name, last_name FROM employees ORDER BY first_name");
        $employees = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = 'Ошибка загрузки сотрудников';
    }
    ?>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-play"></i> Открытие новой смены</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="shifts.php" class="shift-form">
                    <div class="form-group">
                        <label for="employee_id">Сотрудник:</label>
                        <select id="employee_id" name="employee_id" class="form-control" required>
                            <option value="">Выберите сотрудника</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo escape($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="initial_cash">Начальная касса (₽):</label>
                        <input type="number" id="initial_cash" name="initial_cash" class="form-control" 
                               value="10000" min="0" step="100" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="open_shift" class="btn btn-success">
                            <i class="fas fa-play"></i> Открыть смену
                        </button>
                        <a href="shifts.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once 'footer.php';
    exit;
}

// Обработка открытия смены
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['open_shift'])) {
    $employee_id = (int)$_POST['employee_id'];
    $initial_cash = (float)$_POST['initial_cash'];
    
    try {
        // Проверяем, нет ли уже активной смены у этого сотрудника
        $check = $pdo->prepare("SELECT id FROM shifts WHERE employee_id = ? AND status = 'active'");
        $check->execute([$employee_id]);
        
        if ($check->rowCount() > 0) {
            $error = 'У этого сотрудника уже есть активная смена';
        } else {
            $stmt = $pdo->prepare("INSERT INTO shifts (employee_id, initial_cash, start_time, status) VALUES (?, ?, NOW(), 'active')");
            $stmt->execute([$employee_id, $initial_cash]);
            $message = 'Смена успешно открыта!';
        }
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

// Закрытие смены
if (isset($_GET['close'])) {
    $id = (int)$_GET['close'];
    
    // Получаем данные смены
    $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
    $stmt->execute([$id]);
    $shift = $stmt->fetch();
    
    if ($shift) {
        ?>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-stop"></i> Закрытие смены #<?php echo $id; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="shifts.php" class="shift-form">
                        <input type="hidden" name="shift_id" value="<?php echo $id; ?>">
                        
                        <div class="form-group">
                            <label>Сотрудник:</label>
                            <?php
                            $emp_stmt = $pdo->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
                            $emp_stmt->execute([$shift['employee_id']]);
                            $emp = $emp_stmt->fetch();
                            ?>
                            <p class="form-control-static"><?php echo escape($emp['first_name'] . ' ' . $emp['last_name']); ?></p>
                        </div>
                        
                        <div class="form-group">
                            <label>Начало смены:</label>
                            <p class="form-control-static"><?php echo date('d.m.Y H:i', strtotime($shift['start_time'])); ?></p>
                        </div>
                        
                        <div class="form-group">
                            <label>Начальная касса:</label>
                            <p class="form-control-static"><?php echo number_format($shift['initial_cash'], 2); ?> ₽</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="final_cash">Конечная касса (₽):</label>
                            <input type="number" id="final_cash" name="final_cash" class="form-control" 
                                   value="<?php echo $shift['initial_cash']; ?>" min="0" step="100" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="close_shift" class="btn btn-warning">
                                <i class="fas fa-stop"></i> Закрыть смену
                            </button>
                            <a href="shifts.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        require_once 'footer.php';
        exit;
    }
}

// Обработка закрытия смены
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_shift'])) {
    $shift_id = (int)$_POST['shift_id'];
    $final_cash = (float)$_POST['final_cash'];
    
    try {
        $stmt = $pdo->prepare("UPDATE shifts SET end_time = NOW(), final_cash = ?, status = 'closed' WHERE id = ?");
        $stmt->execute([$final_cash, $shift_id]);
        $message = 'Смена успешно закрыта!';
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

// Редактирование смены
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    
    // Получаем данные смены
    $stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
    $stmt->execute([$id]);
    $shift = $stmt->fetch();
    
    if ($shift) {
        // Получаем список сотрудников
        $employees = $pdo->query("SELECT id, first_name, last_name FROM employees ORDER BY first_name")->fetchAll();
        ?>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> Редактирование смены #<?php echo $id; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="shifts.php" class="shift-form">
                        <input type="hidden" name="shift_id" value="<?php echo $id; ?>">
                        
                        <div class="form-group">
                            <label for="edit_employee_id">Сотрудник:</label>
                            <select id="edit_employee_id" name="employee_id" class="form-control" required>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>" <?php echo $emp['id'] == $shift['employee_id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_start_time">Начало смены:</label>
                            <input type="datetime-local" id="edit_start_time" name="start_time" class="form-control" 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($shift['start_time'])); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_end_time">Конец смены:</label>
                            <input type="datetime-local" id="edit_end_time" name="end_time" class="form-control" 
                                   value="<?php echo $shift['end_time'] ? date('Y-m-d\TH:i', strtotime($shift['end_time'])) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_initial_cash">Начальная касса (₽):</label>
                            <input type="number" id="edit_initial_cash" name="initial_cash" class="form-control" 
                                   value="<?php echo $shift['initial_cash']; ?>" min="0" step="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_final_cash">Конечная касса (₽):</label>
                            <input type="number" id="edit_final_cash" name="final_cash" class="form-control" 
                                   value="<?php echo $shift['final_cash']; ?>" min="0" step="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status">Статус:</label>
                            <select id="edit_status" name="status" class="form-control" required>
                                <option value="active" <?php echo $shift['status'] == 'active' ? 'selected' : ''; ?>>Активна</option>
                                <option value="closed" <?php echo $shift['status'] == 'closed' ? 'selected' : ''; ?>>Закрыта</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="edit_shift" class="btn btn-success">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                            <a href="shifts.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        require_once 'footer.php';
        exit;
    }
}

// Обработка редактирования смены
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_shift'])) {
    $shift_id = (int)$_POST['shift_id'];
    $employee_id = (int)$_POST['employee_id'];
    $start_time = $_POST['start_time'];
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
    $initial_cash = (float)$_POST['initial_cash'];
    $final_cash = !empty($_POST['final_cash']) ? (float)$_POST['final_cash'] : null;
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE shifts 
            SET employee_id = ?, start_time = ?, end_time = ?, initial_cash = ?, final_cash = ?, status = ? 
            WHERE id = ?
        ");
        $stmt->execute([$employee_id, $start_time, $end_time, $initial_cash, $final_cash, $status, $shift_id]);
        $message = 'Смена успешно обновлена!';
    } catch (Exception $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
}

// Удаление смены
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Смена удалена';
    } catch (Exception $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
}

// Получаем список смен
$shifts = [];
try {
    $stmt = $pdo->query("
        SELECT s.*, e.first_name, e.last_name, e.position 
        FROM shifts s 
        JOIN employees e ON s.employee_id = e.id 
        ORDER BY s.start_time DESC
    ");
    $shifts = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Ошибка загрузки данных: ' . $e->getMessage();
}
?>

<div class="container">
    <h1><i class="fas fa-clock"></i> Управление сменами</h1>
    
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
            <h3><i class="fas fa-list"></i> Список смен (<?php echo count($shifts); ?>)</h3>
            <a href="?new" class="btn btn-primary">
                <i class="fas fa-plus"></i> Открыть смену
            </a>
        </div>
        
        <div class="card-body">
            <?php if (count($shifts) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Сотрудник</th>
                            <th>Должность</th>
                            <th>Начало</th>
                            <th>Конец</th>
                            <th>Начальная касса</th>
                            <th>Конечная касса</th>
                            <th>Разница</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shifts as $row): 
                            $difference = $row['final_cash'] ? $row['final_cash'] - $row['initial_cash'] : 0;
                        ?>
                        <tr>
                            <td><?php echo escape($row['id']); ?></td>
                            <td>
                                <strong><?php echo escape($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                            </td>
                            <td><?php echo escape($row['position']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($row['start_time'])); ?></td>
                            <td>
                                <?php if ($row['end_time']): ?>
                                    <?php echo date('d.m.Y H:i', strtotime($row['end_time'])); ?>
                                <?php else: ?>
                                    <em class="text-muted">Не завершена</em>
                                <?php endif; ?>
                            </td>
                            <td class="amount-cell"><?php echo number_format($row['initial_cash'], 0); ?> ₽</td>
                            <td class="amount-cell">
                                <?php if ($row['final_cash']): ?>
                                    <?php echo number_format($row['final_cash'], 0); ?> ₽
                                <?php else: ?>
                                    <em class="text-muted">—</em>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $difference >= 0 ? 'positive' : 'negative'; ?>">
                                <?php if ($row['final_cash']): ?>
                                    <?php echo $difference >= 0 ? '+' : ''; ?><?php echo number_format($difference, 0); ?> ₽
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status status-<?php echo $row['status']; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Активна' : 'Закрыта'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($row['status'] == 'active'): ?>
                                        <a href="?close=<?php echo $row['id']; ?>" class="btn-action btn-warning" title="Закрыть смену">
                                            <i class="fas fa-stop"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Удалить"
                                       onclick="return confirm('Удалить смену?')">
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
                <i class="fas fa-clock" style="font-size: 4rem; opacity: 0.3;"></i>
                <p>Нет данных о сменах</p>
                <a href="?new" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Открыть первую смену
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.shift-form {
    max-width: 600px;
    margin: 0 auto;
}

.shift-form .form-group {
    margin-bottom: 20px;
}

.shift-form label {
    display: block;
    color: var(--accent);
    margin-bottom: 8px;
    font-weight: 500;
}

.shift-form .form-control {
    width: 100%;
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 8px;
    color: white;
    font-size: 1rem;
}

.shift-form .form-control:focus {
    outline: none;
    border-color: var(--secondary);
}

.shift-form .form-control-static {
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    color: var(--secondary-light);
}

.positive {
    color: #4caf50;
    font-weight: bold;
}

.negative {
    color: #f44336;
    font-weight: bold;
}

.status-active {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
    border: 1px solid #4caf50;
}

.status-closed {
    background: rgba(158, 158, 158, 0.2);
    color: #9e9e9e;
    border: 1px solid #9e9e9e;
}

.btn-warning {
    background: rgba(255, 152, 0, 0.2);
    color: #ff9800;
}

.btn-warning:hover {
    background: rgba(255, 152, 0, 0.4);
}
</style>

<?php require_once 'footer.php'; ?>