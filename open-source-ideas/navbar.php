<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">💡 Платформа идей</a>
        <div class="nav-links">
            <a href="index.php" <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : '' ?>>Главная</a>
            <a href="search.php" <?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'class="active"' : '' ?>>🔍 Поиск</a>
            <a href="ratings.php" <?= basename($_SERVER['PHP_SELF']) == 'ratings.php' ? 'class="active"' : '' ?>>🏆 Рейтинги</a>
            <a href="hall_of_fame.php" <?= basename($_SERVER['PHP_SELF']) == 'hall_of_fame.php' ? 'class="active"' : '' ?>>Зал славы</a>
            <?php if (isLoggedIn()): ?>
                <a href="add_idea.php" <?= basename($_SERVER['PHP_SELF']) == 'add_idea.php' ? 'class="active"' : '' ?>>➕ Добавить идею</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php" <?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'class="active"' : '' ?>>Админка</a>
                <?php endif; ?>
                <a href="profile.php?id=<?= $_SESSION['user_id'] ?>" class="user-info">
                    👤 <?= h($_SESSION['username']) ?>
                </a>
                <a href="logout.php">Выход</a>
                <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">
                    🌙
                </button>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>