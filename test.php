<?php
// test.php
require_once 'config.php';
echo "<h1>Тест подключения к БД</h1>";
echo "Подключение к базе данных успешно!<br>";
echo "Хост: " . DB_HOST . "<br>";
echo "База данных: " . DB_NAME . "<br>";

// Тест сессии
echo "<h2>Тест сессии</h2>";
print_r($_SESSION);

// Тест запроса
try {
    $stmt = $pdo->query("SHOW TABLES");
    echo "<h2>Таблицы в базе данных:</h2>";
    while ($row = $stmt->fetch()) {
        echo $row['Tables_in_' . DB_NAME] . "<br>";
    }
} catch (Exception $e) {
    echo "Ошибка запроса: " . $e->getMessage();
}