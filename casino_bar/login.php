<?php
// login.php - ОБНОВЛЕННАЯ СТРАНИЦА ВХОДА И РЕГИСТРАЦИИ
require_once 'config.php';

// Если уже авторизован, перенаправляем на главную
if (!isGuest()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Демо-авторизация
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        $_SESSION['first_name'] = 'Администратор';
        $_SESSION['last_name'] = 'Казино';
        header('Location: index.php');
        exit;
    } elseif ($username === 'employee' && $password === 'emp123') {
        $_SESSION['user_id'] = 2;
        $_SESSION['role'] = 'employee';
        $_SESSION['first_name'] = 'Сотрудник';
        $_SESSION['last_name'] = 'Казино';
        header('Location: index.php');
        exit;
    } elseif ($username === 'vip' && $password === 'vip123') {
        $_SESSION['user_id'] = 3;
        $_SESSION['role'] = 'vip';
        $_SESSION['first_name'] = 'VIP';
        $_SESSION['last_name'] = 'Гость';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверное имя пользователя или пароль';
    }
}

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Валидация
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'Имя обязательно для заполнения';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Фамилия обязательна для заполнения';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    }
    
    if (empty($username)) {
        $errors[] = 'Имя пользователя обязательно';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Имя пользователя должно быть не менее 4 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        // В демо-режиме просто показываем успех
        $success = 'Регистрация прошла успешно! Теперь вы можете войти.';
        // В реальной системе здесь было бы сохранение в БД
        $activeTab = 'login';
    } else {
        $error = implode('<br>', $errors);
    }
}

// Демо-вход
if (isset($_GET['demo'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['role'] = 'guest';
    $_SESSION['first_name'] = 'Демо';
    $_SESSION['last_name'] = 'Гость';
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход / Регистрация - Элитное Казино</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
            color: #F5F5F5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            width: 100%;
            max-width: 500px;
        }
        
        .auth-card {
            background: rgba(26, 26, 26, 0.95);
            border-radius: 20px;
            padding: 40px;
            border: 2px solid #D4AF37;
            box-shadow: 0 20px 50px rgba(199, 21, 133, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            color: #D4AF37;
            font-size: 3.5rem;
            margin-bottom: 15px;
        }
        
        .logo h1 {
            color: #D4AF37;
            font-family: 'Cinzel', serif;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #888;
            font-size: 1rem;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            font-weight: bold;
            color: #888;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab.active {
            color: #D4AF37;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #C71585, #D4AF37);
        }
        
        .tab i {
            margin-right: 8px;
        }
        
        .tab:hover {
            color: #C71585;
        }
        
        .form-container {
            transition: all 0.3s;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        label {
            display: block;
            color: #D4AF37;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #C71585;
            font-size: 1.1rem;
        }
        
        input, select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #C71585;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn-auth {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #C71585, #8B0000);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-auth:hover {
            background: linear-gradient(45deg, #8B0000, #C71585);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(199, 21, 133, 0.3);
        }
        
        .btn-demo {
            display: block;
            width: 100%;
            padding: 16px;
            background: rgba(212, 175, 55, 0.15);
            color: #D4AF37;
            border: 1px solid #D4AF37;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn-demo:hover {
            background: rgba(212, 175, 55, 0.3);
            transform: translateY(-3px);
        }
        
        .error {
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid #8B0000;
            color: #FF7F7F;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .success {
            background: rgba(0, 128, 0, 0.2);
            border: 1px solid #00ff00;
            color: #90EE90;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .info-box {
            background: rgba(23, 162, 184, 0.1);
            border: 1px solid #17a2b8;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .info-box h4 {
            color: #17a2b8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box p {
            color: #888;
            font-size: 0.9rem;
            margin: 5px 0;
        }
        
        .info-box .credentials {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: monospace;
        }
        
        .credentials span {
            color: #D4AF37;
            font-weight: bold;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #888;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #D4AF37;
        }
        
        @media (max-width: 768px) {
            .auth-card {
                padding: 25px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 25px;
            }
            
            .logo h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo">
                <i class="fas fa-gem"></i>
                <h1>Элитное Казино</h1>
                <p>Система управления премиальным заведением</p>
            </div>
            
            <!-- Вкладки -->
            <div class="tabs">
                <div class="tab <?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Вход
                </div>
                <div class="tab <?php echo $activeTab === 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Регистрация
                </div>
            </div>
            
            <!-- Сообщения -->
            <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <!-- Форма входа -->
            <div id="loginForm" class="form-container" style="display: <?php echo $activeTab === 'login' ? 'block' : 'none'; ?>;">
                <form method="POST" action="login.php?tab=login">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Имя пользователя</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" 
                                   placeholder="Введите имя пользователя" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" 
                                   placeholder="Введите пароль" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="login" class="btn-auth">
                        <i class="fas fa-sign-in-alt"></i> Войти в систему
                    </button>
                </form>
                
                <a href="?demo=1" class="btn-demo">
                    <i class="fas fa-user-secret"></i> Войти как демо-гость
                </a>
            </div>
            
            <!-- Форма регистрации -->
            <div id="registerForm" class="form-container" style="display: <?php echo $activeTab === 'register' ? 'block' : 'none'; ?>;">
                <form method="POST" action="login.php?tab=register">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Имя</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="first_name" name="first_name" 
                                       placeholder="Иван" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Фамилия</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="last_name" name="last_name" 
                                       placeholder="Петров" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" 
                                   placeholder="ivan@example.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" 
                                   placeholder="+7 (999) 123-45-67" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_username">Имя пользователя</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user-tag"></i>
                            <input type="text" id="reg_username" name="username" 
                                   placeholder="ivan_petrov" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg_password">Пароль</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="reg_password" name="password" 
                                       placeholder="Минимум 6 символов" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Подтверждение</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       placeholder="Повторите пароль" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="btn-auth">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>
                </form>
            </div>
            
            <!-- Информация для входа -->
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Тестовые учетные данные</h4>
                <p>Для демонстрации используйте:</p>
                <div class="credentials">
                    <p><span>Админ:</span> admin / admin123</p>
                    <p><span>Сотрудник:</span> employee / emp123</p>
                    <p><span>VIP гость:</span> vip / vip123</p>
                </div>
            </div>
            
            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Вернуться на главную</a>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Обновляем URL без перезагрузки
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
            
            // Переключаем видимость форм
            document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
            document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
            
            // Обновляем активные вкладки
            document.querySelectorAll('.tab').forEach((tabElement, index) => {
                if ((index === 0 && tab === 'login') || (index === 1 && tab === 'register')) {
                    tabElement.classList.add('active');
                } else {
                    tabElement.classList.remove('active');
                }
            });
        }
        
        // Валидация формы регистрации
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.querySelector('#registerForm form');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const password = document.getElementById('reg_password').value;
                    const confirm = document.getElementById('confirm_password').value;
                    
                    if (password !== confirm) {
                        e.preventDefault();
                        alert('Пароли не совпадают!');
                    }
                    
                    if (password.length < 6) {
                        e.preventDefault();
                        alert('Пароль должен быть не менее 6 символов!');
                    }
                });
            }
        });
    </script>
</body>
</html>