<?php
require_once 'config.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Неверный никнейм или пароль';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка при входе: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Платформа идей</title>
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">💡 Платформа идей</a>
            <div class="nav-links">
                <a href="index.php">Главная</a>
                <a href="hall_of_fame.php">Зал славы</a>
                <a href="login.php" class="active">Вход</a>
                <a href="register.php">Регистрация</a>
                <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">🌙</button>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="auth-form">
            <h1>Вход</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Никнейм</label>
                    <input type="text" id="username" name="username" 
                           value="<?= h($_POST['username'] ?? '') ?>" 
                           required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
            <p class="text-center">
                Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </p>
        </div>
    </div>
</body>
</html>