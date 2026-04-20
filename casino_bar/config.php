<?php
// config.php - ИСПРАВЛЕННАЯ ВЕРСИЯ

// Настройки для отображения ошибок (только для разработки)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Старт сессии ТОЛЬКО если она еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Настройки базы данных
define('DB_HOST', '134.90.167.42:10306');
define('DB_NAME', 'project_Bolshakov');
define('DB_USER', 'Bolshakov');
define('DB_PASS', 'ICRQgAACcNSLDVjN');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
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

function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

function getUserName() {
    return $_SESSION['first_name'] ?? 'Гость';
}

// Функция экранирования
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>