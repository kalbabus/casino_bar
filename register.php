<?php
// register.php
require_once 'config.php';

// Если уже авторизован, перенаправляем на главную
if (!isGuest()) {
    header('Location: index.php');
    exit;
}

// В демо-системе регистрация может быть недоступна
header('Location: login.php');
exit;