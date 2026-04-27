<?php
// config.php - ПОДКЛЮЧЕНИЕ К ДВУМ БАЗАМ ДАННЫХ
session_start();

// Настройки для отображения ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========== ГЛОБАЛЬНАЯ БАЗА ДАННЫХ (project_Bolshakov) ==========
define('DB_HOST_GLOBAL', '134.90.167.42');
define('DB_PORT_GLOBAL', '10306');
define('DB_NAME_GLOBAL', 'project_Bolshakov');
define('DB_USER_GLOBAL', 'Bolshakov');
define('DB_PASS_GLOBAL', 'ICRQgAACcNSLDVjN');

// ========== ЛОКАЛЬНАЯ БАЗА ДАННЫХ (local_project_Bolshakov) ==========
define('DB_HOST_LOCAL', 'localhost');
define('DB_PORT_LOCAL', '3306');
define('DB_NAME_LOCAL', 'local_project_Bolshakov');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');  // Если есть пароль, укажите

// ========== ПОДКЛЮЧЕНИЯ ==========
$pdo_global = null;
$pdo_local = null;
$local_available = false;

// Подключение к ГЛОБАЛЬНОЙ БД
try {
    $pdo_global = new PDO(
        "mysql:host=" . DB_HOST_GLOBAL . ";port=" . DB_PORT_GLOBAL . ";dbname=" . DB_NAME_GLOBAL . ";charset=utf8mb4",
        DB_USER_GLOBAL,
        DB_PASS_GLOBAL,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "<!-- Глобальная БД подключена -->";
} catch (PDOException $e) {
    die("Ошибка подключения к ГЛОБАЛЬНОЙ БД: " . $e->getMessage());
}

// Подключение к ЛОКАЛЬНОЙ БД (если доступна)
try {
    $pdo_local = new PDO(
        "mysql:host=" . DB_HOST_LOCAL . ";port=" . DB_PORT_LOCAL . ";dbname=" . DB_NAME_LOCAL . ";charset=utf8mb4",
        DB_USER_LOCAL,
        DB_PASS_LOCAL,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    $local_available = true;
    echo "<!-- Локальная БД подключена -->";
} catch (PDOException $e) {
    error_log("Локальная БД не доступна: " . $e->getMessage());
    echo "<!-- Локальная БД НЕ доступна, работаем только с глобальной -->";
}

// Основное подключение (для обратной совместимости - используем глобальную)
$pdo = $pdo_global;

// ========== ФУНКЦИИ ДЛЯ РАБОТЫ С ДВУМЯ БД ==========

/**
 * Получить подключение к нужной БД в зависимости от типа данных
 * @param string $data_type - тип данных: 'important' (важные) или 'unimportant' (неважные)
 * @return PDO
 */
function getDB($data_type = 'important') {
    global $pdo_global, $pdo_local, $local_available;
    
    // Неважные данные (меню, детали заказов, сессии) - из локальной БД
    if ($data_type === 'unimportant' && $local_available) {
        return $pdo_local;
    }
    
    // Важные данные (пользователи, заказы, гости, смены, сотрудники) - из глобальной
    return $pdo_global;
}

/**
 * Выполнить запрос с автоматическим выбором БД
 * @param string $sql - SQL запрос
 * @param array $params - параметры
 * @param string $table_name - имя таблицы (для определения типа)
 * @return PDOStatement
 */
function queryDB($sql, $params = [], $table_name = null) {
    // Таблицы, которые хранятся в локальной БД (неважные)
    $local_tables = ['menu_items', 'order_details', 'guest_sessions'];
    
    $use_local = false;
    if ($table_name && in_array($table_name, $local_tables)) {
        $use_local = true;
    }
    
    $db = getDB($use_local ? 'unimportant' : 'important');
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Получить данные из таблицы (автоматический выбор БД)
 * @param string $table_name - имя таблицы
 * @param string $where - условие WHERE
 * @param string $order - сортировка
 * @return array
 */
function getTableData($table_name, $where = "1=1", $order = "") {
    $local_tables = ['menu_items', 'order_details', 'guest_sessions'];
    $use_local = in_array($table_name, $local_tables);
    
    $db = getDB($use_local ? 'unimportant' : 'important');
    $sql = "SELECT * FROM `$table_name` WHERE $where $order";
    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

/**
 * Вставить данные в таблицу (автоматический выбор БД)
 * @param string $table_name - имя таблицы
 * @param array $data - ассоциативный массив данных
 * @return int|false
 */
function insertIntoTable($table_name, $data) {
    $local_tables = ['menu_items', 'order_details', 'guest_sessions'];
    $use_local = in_array($table_name, $local_tables);
    
    $db = getDB($use_local ? 'unimportant' : 'important');
    
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $sql = "INSERT INTO `$table_name` ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    if ($stmt->execute()) {
        return (int)$db->lastInsertId();
    }
    return false;
}

/**
 * Обновить данные в таблице
 * @param string $table_name - имя таблицы
 * @param array $data - данные для обновления
 * @param string $where - условие
 * @return bool
 */
function updateTable($table_name, $data, $where) {
    $local_tables = ['menu_items', 'order_details', 'guest_sessions'];
    $use_local = in_array($table_name, $local_tables);
    
    $db = getDB($use_local ? 'unimportant' : 'important');
    
    $set = [];
    foreach (array_keys($data) as $col) {
        $set[] = "`$col` = :$col";
    }
    $set_str = implode(', ', $set);
    
    $sql = "UPDATE `$table_name` SET $set_str WHERE $where";
    $stmt = $db->prepare($sql);
    
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    return $stmt->execute();
}

/**
 * Удалить данные из таблицы
 * @param string $table_name - имя таблицы
 * @param string $where - условие
 * @return bool
 */
function deleteFromTable($table_name, $where) {
    $local_tables = ['menu_items', 'order_details', 'guest_sessions'];
    $use_local = in_array($table_name, $local_tables);
    
    $db = getDB($use_local ? 'unimportant' : 'important');
    $sql = "DELETE FROM `$table_name` WHERE $where";
    return $db->exec($sql);
}

// ========== ФУНКЦИИ АВТОРИЗАЦИИ (без изменений) ==========

function isGuest() {
    return !isset($_SESSION['user_id']) || $_SESSION['role'] == 'guest';
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isEmployee() {
    return isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'employee');
}

function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

function getUserName() {
    return $_SESSION['first_name'] ?? 'Гость';
}

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function generateCaptcha() {
    $num1 = rand(1, 20);
    $num2 = rand(1, 20);
    $_SESSION['captcha_result'] = $num1 + $num2;
    $_SESSION['captcha_text'] = "$num1 + $num2 = ?";
    return $_SESSION['captcha_text'];
}

function verifyCaptcha($answer) {
    return isset($_SESSION['captcha_result']) && (int)$answer === (int)$_SESSION['captcha_result'];
}

function validatePasswordLength($password) {
    $len = strlen($password);
    return $len >= 4 && $len <= 10;
}

function getPasswordError($password) {
    $len = strlen($password);
    if ($len < 4) return 'Пароль должен быть не менее 4 символов';
    if ($len > 10) return 'Пароль должен быть не более 10 символов';
    return null;
}

// Функция для отладки (показать статус БД)
function showDBStatus() {
    global $local_available;
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo '<div style="position:fixed; bottom:10px; right:10px; background:#333; color:#fff; padding:5px 10px; border-radius:5px; font-size:12px; z-index:9999;">';
        echo '🌍 Глобальная БД: ✓ | ';
        echo $local_available ? '🏠 Локальная БД: ✓' : '🏠 Локальная БД: ✗';
        echo '</div>';
    }
}
?>