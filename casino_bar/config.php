<?php
// config.php - ГЛАВНЫЙ КОНФИГУРАЦИОННЫЙ ФАЙЛ
session_start();

// Настройки для отображения ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Настройки базы данных
define('DB_HOST', '134.90.167.42');
define('DB_PORT', '10306');
define('DB_NAME', 'project_Bolshakov');
define('DB_USER', 'Bolshakov');
define('DB_PASS', 'ICRQgAACcNSLDVjN');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функции для проверки авторизации
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

// Функции капчи
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
// Добавьте в config.php после других функций

// Функция проверки пароля (мин 4, макс 10)
function validatePasswordLength($password) {
    $len = strlen($password);
    return $len >= 4 && $len <= 10;
}

// Функция получения ошибки пароля
function getPasswordError($password) {
    $len = strlen($password);
    if ($len < 4) return 'Пароль должен быть не менее 4 символов';
    if ($len > 10) return 'Пароль должен быть не более 10 символов';
    return null;
}
?>