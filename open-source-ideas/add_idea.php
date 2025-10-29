<?php
require_once 'config.php';
require_once 'functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    if (empty($title) || empty($description)) {
        $error = 'Заполните название и описание';
    } elseif (strlen($title) < 5 || strlen($title) > 255) {
        $error = 'Название должно быть от 5 до 255 символов';
    } elseif (strlen($description) < 20) {
        $error = 'Описание должно быть минимум 20 символов';
    } else {
        try {
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageName = uploadImage($_FILES['image']);
            }
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO ideas (user_id, title, description, tags, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $description,
                $tags,
                $imageName
            ]);
            $ideaId = $db->lastInsertId();
            checkAndAwardBadges($_SESSION['user_id']);
            header('Location: idea.php?id=' . $ideaId);
            exit;
        } catch (Exception $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить идею - Поделись своей идеей с сообществом разработчиков</title>
    <meta name="description" content="Опубликуй свою идею для приложения, сайта или стартапа. Разработчики помогут воплотить её в жизнь. Совместная разработка и open source проекты.">
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="form-container">
            <h1>Добавить новую идею</h1>
            <p class="subtitle">Опишите вашу идею, и разработчики помогут её воплотить!</p>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Название идеи *</label>
                    <input type="text" id="title" name="title" 
                           value="<?= h($_POST['title'] ?? '') ?>" 
                           placeholder="Например: Приложение для учёта личных финансов"
                           required minlength="5" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="description">Подробное описание *</label>
                    <textarea id="description" name="description" rows="10"
                              placeholder="Опишите вашу идею подробно: какая проблема должна решаться, основные функции, целевая аудитория..."
                              required minlength="20"><?= h($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="tags">Теги</label>
                    <input type="text" id="tags" name="tags" 
                           value="<?= h($_POST['tags'] ?? '') ?>"
                           placeholder="Python, JavaScript, Мобильное приложение, Web">
                    <small>Укажите теги через запятую (языки программирования, технологии, платформы)</small>
                </div>
                <div class="form-group">
                    <label for="image">Изображение</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Загрузите изображение (макет, схему или иллюстрацию). Максимум 25 МБ.</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Опубликовать идею</button>
                    <a href="index.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>