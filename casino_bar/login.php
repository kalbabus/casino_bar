<?php
// login.php - БЕЗ VIP
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Если уже авторизован
if (!isGuest()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'login';

// Функции капчи
if (!function_exists('generateCaptcha')) {
    function generateCaptcha() {
        $num1 = rand(1, 20);
        $num2 = rand(1, 20);
        $_SESSION['captcha_result'] = $num1 + $num2;
        $_SESSION['captcha_text'] = "$num1 + $num2 = ?";
        return $_SESSION['captcha_text'];
    }
}

if (!function_exists('verifyCaptcha')) {
    function verifyCaptcha($answer) {
        return isset($_SESSION['captcha_result']) && (int)$answer === (int)$_SESSION['captcha_result'];
    }
}

// Функция проверки пароля
function verifyPassword($input_password, $stored_hash, $username) {
    if (password_verify($input_password, $stored_hash)) return true;
    if (md5($input_password) === $stored_hash) return true;
    if ($input_password === $stored_hash) return true;
    
    // Только admin и employee
    $test_passwords = ['admin123', 'emp123'];
    if (in_array($input_password, $test_passwords) && in_array($username, ['admin', 'employee'])) {
        return true;
    }
    return false;
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = (int)($_POST['captcha'] ?? 0);
    
    if (!verifyCaptcha($captcha)) {
        $error = 'Неверный ответ капчи!';
        generateCaptcha();
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                if (verifyPassword($password, $user['password'], $username)) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'] ?? $username;
                    $_SESSION['last_name'] = $user['last_name'] ?? '';
                    
                    $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Неверный пароль!';
                }
            } else {
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE login = ? AND is_active = 1");
                $stmt->execute([$username]);
                $employee = $stmt->fetch();
                
                if ($employee) {
                    if (verifyPassword($password, $employee['password_hash'] ?? '', $username)) {
                        $_SESSION['user_id'] = $employee['id'];
                        $_SESSION['username'] = $employee['login'];
                        $_SESSION['role'] = 'employee';
                        $_SESSION['first_name'] = $employee['first_name'];
                        $_SESSION['last_name'] = $employee['last_name'];
                        
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'Неверный пароль!';
                    }
                } else {
                    $error = 'Пользователь не найден!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $document_number = trim($_POST['document_number'] ?? '');
    $captcha = (int)($_POST['captcha'] ?? 0);
    
    if (!verifyCaptcha($captcha)) {
        $error = 'Неверный ответ капчи!';
        generateCaptcha();
    } else {
        $errors = [];
        
        if (empty($first_name)) $errors[] = 'Имя обязательно';
        if (empty($last_name)) $errors[] = 'Фамилия обязательна';
        if (empty($email)) $errors[] = 'Email обязателен';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Неверный email';
        if (empty($phone)) $errors[] = 'Телефон обязателен';
        if (empty($username) || strlen($username) < 4) $errors[] = 'Имя пользователя (мин 4 символа)';
        if (strlen($username) > 20) $errors[] = 'Имя пользователя (макс 20 символов)';
        
        if (empty($password)) {
            $errors[] = 'Пароль обязателен';
        } elseif (strlen($password) < 4) {
            $errors[] = 'Пароль должен быть не менее 4 символов';
        } elseif (strlen($password) > 10) {
            $errors[] = 'Пароль должен быть не более 10 символов';
        }
        
        if ($password !== $confirm_password) $errors[] = 'Пароли не совпадают';
        if (empty($birth_date)) $errors[] = 'Дата рождения обязательна';
        if (empty($document_number)) $errors[] = 'Номер документа обязателен';
        
        if (empty($errors)) {
            try {
                $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $check->execute([$username, $email]);
                
                if ($check->rowCount() > 0) {
                    $error = 'Пользователь с таким именем или email уже существует!';
                } else {
                    $pdo->beginTransaction();
                    
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password, email, first_name, last_name, phone, role, created_at, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, 'guest', NOW(), 1)
                    ");
                    $stmt->execute([$username, $hashedPassword, $email, $first_name, $last_name, $phone]);
                    $user_id = $pdo->lastInsertId();
                    
                    $stmt2 = $pdo->prepare("
                        INSERT INTO guests (first_name, last_name, phone, birth_date, document_number, visits_count, total_spent, registration_date) 
                        VALUES (?, ?, ?, ?, ?, 0, 0, NOW())
                    ");
                    $stmt2->execute([$first_name, $last_name, $phone, $birth_date, $document_number]);
                    
                    $pdo->commit();
                    
                    $success = 'Регистрация успешна! Теперь вы можете войти.';
                    $activeTab = 'login';
                    generateCaptcha();
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'Ошибка регистрации: ' . $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

$captchaQuestion = generateCaptcha();

// Демо-вход
if (isset($_GET['demo'])) {
    $_SESSION['user_id'] = 999;
    $_SESSION['role'] = 'guest';
    $_SESSION['first_name'] = 'Демо';
    $_SESSION['last_name'] = 'Гость';
    $_SESSION['username'] = 'demo';
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
        }
        .auth-container {
            max-width: 550px;
            width: 100%;
        }
        .captcha-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .captcha-question {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 10px;
        }
        .input-with-icon {
            position: relative;
        }
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        .input-with-icon input {
            padding-left: 45px;
        }
        .credentials {
            background: rgba(0, 0, 0, 0.3);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 0.85rem;
        }
        .credentials span {
            color: var(--accent);
            font-weight: bold;
        }
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            font-weight: bold;
            color: #888;
            transition: all 0.3s;
        }
        .tab.active {
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
            margin-bottom: -2px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo i {
            font-size: 3rem;
            color: var(--accent);
        }
        .logo h1 {
            margin-top: 10px;
            font-size: 1.8rem;
        }
        .info-box {
            margin-top: 30px;
            padding: 15px;
            background: rgba(23, 162, 184, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }
        .info-box h4 {
            color: #17a2b8;
            margin-bottom: 10px;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff7f7f;
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #98fb98;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--accent);
        }
        .form-group label .required {
            color: #dc3545;
        }
        .form-group small {
            display: block;
            color: var(--secondary-light);
            font-size: 0.75rem;
            margin-top: 5px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }
        .password-requirements {
            background: rgba(255, 193, 7, 0.1);
            border-left: 3px solid #ffc107;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 0.8rem;
            border-radius: 5px;
        }
        .password-requirements i {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="card" style="padding: 40px;">
            <div class="logo">
                <i class="fas fa-gem"></i>
                <h1>Элитное Казино</h1>
                <p style="color: var(--secondary-light);">Система управления</p>
            </div>
            
            <div class="tabs">
                <div class="tab <?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Вход
                </div>
                <div class="tab <?php echo $activeTab === 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Регистрация
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <!-- Форма входа -->
            <div id="loginForm" style="display: <?php echo $activeTab === 'login' ? 'block' : 'none'; ?>">
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label>Имя пользователя</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" class="form-control" required placeholder="admin / employee">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="captcha-container">
                        <div class="captcha-question"><?php echo $captchaQuestion; ?></div>
                        <input type="number" name="captcha" class="form-control" placeholder="Введите ответ" required style="padding-left: 15px;">
                    </div>
                    <button type="submit" class="btn btn-success">Войти</button>
                </form>
                <a href="?demo=1" class="btn btn-primary" style="display:block; text-align:center; margin-top:15px; text-decoration:none;">Демо-гость</a>
            </div>
            
            <!-- Форма регистрации -->
            <div id="registerForm" style="display: <?php echo $activeTab === 'register' ? 'block' : 'none'; ?>">
                <form method="POST" onsubmit="return validatePassword()">
                    <input type="hidden" name="register" value="1">
                    
                    <div class="form-group">
                        <label>Имя <span class="required">*</span></label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Фамилия <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон <span class="required">*</span></label>
                        <input type="text" name="phone" class="form-control" required placeholder="+7 (999) 123-45-67">
                    </div>
                    
                    <div class="form-group">
                        <label>Дата рождения <span class="required">*</span></label>
                        <input type="date" name="birth_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Номер документа <span class="required">*</span></label>
                        <input type="text" name="document_number" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Имя пользователя <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" required minlength="4" maxlength="20">
                        <small>От 4 до 20 символов</small>
                    </div>
                    
                    <div class="password-requirements">
                        <i class="fas fa-info-circle"></i> <strong>Требования к паролю:</strong><br>
                        • Минимум 4 символа<br>
                        • Максимум 10 символов
                    </div>
                    
                    <div class="form-group">
                        <label>Пароль <span class="required">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="4" maxlength="10">
                        <small id="password_length">Длина: 0/10 символов</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Подтверждение пароля <span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <small id="password_match" style="color: #ff7f7f;"></small>
                    </div>
                    
                    <div class="captcha-container">
                        <div class="captcha-question"><?php echo $captchaQuestion; ?></div>
                        <input type="number" name="captcha" class="form-control" placeholder="Введите ответ" required style="padding-left: 15px;">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                </form>
            </div>
            
            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Тестовые учетные данные</h4>
                <div class="credentials">
                    <p><span>👑 Администратор:</span> admin / admin123</p>
                    <p><span>👤 Сотрудник:</span> employee / emp123</p>
                    <p><span>📝 Или зарегистрируйтесь сами!</span></p>
                    <p><span>🔒 Пароль: от 4 до 10 символов</span></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
            document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
            
            document.querySelectorAll('.tab').forEach((el, i) => {
                if ((i === 0 && tab === 'login') || (i === 1 && tab === 'register')) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }
        
        // Проверка длины пароля в реальном времени
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const lengthDisplay = document.getElementById('password_length');
        const matchDisplay = document.getElementById('password_match');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const len = this.value.length;
                lengthDisplay.innerHTML = `Длина: ${len}/10 символов`;
                if (len > 10) {
                    this.value = this.value.slice(0, 10);
                    lengthDisplay.innerHTML = `Длина: 10/10 символов (максимум)`;
                }
                checkPasswordMatch();
            });
        }
        
        if (confirmInput) {
            confirmInput.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordMatch() {
            if (passwordInput && confirmInput) {
                if (confirmInput.value.length > 0) {
                    if (passwordInput.value === confirmInput.value) {
                        matchDisplay.innerHTML = '✓ Пароли совпадают';
                        matchDisplay.style.color = '#28a745';
                    } else {
                        matchDisplay.innerHTML = '✗ Пароли не совпадают';
                        matchDisplay.style.color = '#dc3545';
                    }
                } else {
                    matchDisplay.innerHTML = '';
                }
            }
        }
        
        function validatePassword() {
            const password = document.getElementById('password').value;
            if (password.length < 4) {
                alert('Пароль должен быть не менее 4 символов!');
                return false;
            }
            if (password.length > 10) {
                alert('Пароль должен быть не более 10 символов!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>