<?php
require_once 'config.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = 'Никнейм должен быть от 3 до 50 символов';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть минимум 6 символов';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким никнеймом уже существует';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hashedPassword]);
                $success = 'Регистрация успешна! Теперь вы можете войти.';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при регистрации: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Платформа идей для разработчиков и стартапов</title>
    <meta name="description" content="Присоединяйся к сообществу разработчиков! Делись идеями, находи проекты для реализации, участвуй в open source разработке.">
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💡 Платформа идей</a>
            <div class="nav-links">
                <a href="index.php">Главная</a>
                <a href="hall_of_fame.php">Зал славы</a>
                <a href="login.php">Вход</a>
                <a href="register.php" class="active">Регистрация</a>
                <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">🌙</button>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="auth-form">
            <h1>Регистрация</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= h($success) ?>
                    <br><a href="login.php">Перейти к входу</a>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Никнейм</label>
                    <input type="text" id="username" name="username" 
                           value="<?= h($_POST['username'] ?? '') ?>" 
                           required minlength="3" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" 
                           required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтвердите пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            </form>
            <p class="text-center">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </p>
        </div>
    </div>
</body>
</html>