<?php
require_once 'config.php';

echo "<h1>Проверка таблиц</h1>";

// Проверяем users
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 10");
echo "<h2>Последние 10 пользователей (users):</h2>";
echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Email</th><th>Имя</th><th>Фамилия</th></tr>";
while ($row = $users->fetch()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['email']}</td><td>{$row['first_name']}</td><td>{$row['last_name']}</td></tr>";
}
echo "</table>";

// Проверяем guests
$guests = $pdo->query("SELECT * FROM guests ORDER BY id DESC LIMIT 10");
echo "<h2>Последние 10 гостей (guests):</h2>";
echo "<table border='1'><tr><th>ID</th><th>Имя</th><th>Фамилия</th><th>Телефон</th></tr>";
while ($row = $guests->fetch()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['first_name']}</td><td>{$row['last_name']}</td><td>{$row['phone']}</td></tr>";
}
echo "</table>";
?>