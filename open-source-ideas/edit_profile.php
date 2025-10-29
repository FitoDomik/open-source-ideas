<?php
require_once 'config.php';
require_once 'functions.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
$db = getDB();
$error = '';
$success = '';
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    if (strlen($bio) > 500) {
        $error = 'Биография слишком длинная (максимум 500 символов)';
    } elseif ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = 'Некорректный URL сайта';
    } else {
        $avatarPath = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'upload/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['avatar']['type'];
            if (!in_array($fileType, $allowedTypes)) {
                $error = 'Разрешены только изображения (JPEG, PNG, GIF, WEBP)';
            } elseif ($_FILES['avatar']['size'] > 25 * 1024 * 1024) {
                $error = 'Файл слишком большой (максимум 25 МБ)';
            } else {
                $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                    if ($user['avatar'] && file_exists('upload/avatars/' . $user['avatar'])) {
                        unlink('upload/avatars/' . $user['avatar']);
                    }
                    $avatarPath = $filename;
                } else {
                    $error = 'Ошибка загрузки файла';
                }
            }
        }
        if (!$error) {
            $stmt = $db->prepare("
                UPDATE users 
                SET avatar = ?, bio = ?, website = ?, github = ?, twitter = ?
                WHERE id = ?
            ");
            if ($stmt->execute([$avatarPath, $bio, $website, $github, $twitter, $userId])) {
                $success = 'Профиль успешно обновлен!';
                $user['avatar'] = $avatarPath;
                $user['bio'] = $bio;
                $user['website'] = $website;
                $user['github'] = $github;
                $user['twitter'] = $twitter;
            } else {
                $error = 'Ошибка при сохранении данных';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать профиль - Платформа идей</title>
    <?php include 'includes_head.php'; ?>
    <style>
        .profile-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--surface);
            border-radius: 12px;
        }
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }
        .form-hint {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 5px;
        }
        .char-counter {
            font-size: 12px;
            color: var(--text-secondary);
            text-align: right;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="form-container">
            <h1>⚙️ Редактировать профиль</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= h($success) ?>
                    <a href="profile.php" style="margin-left: 10px;">Вернуться в профиль →</a>
                </div>
            <?php endif; ?>
            <div class="profile-preview">
                <img src="<?= getUserAvatar($user['avatar'], $user['username']) ?>" 
                     alt="<?= h($user['username']) ?>" 
                     class="avatar-preview"
                     id="avatar-preview">
                <div>
                    <h3><?= h($user['username']) ?></h3>
                    <p style="color: var(--text-secondary);">Зарегистрирован: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="avatar">Аватар</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                    <div class="form-hint">Разрешены JPEG, PNG, GIF, WEBP. Максимум 25 МБ</div>
                </div>
                <div class="form-group">
                    <label for="bio">О себе</label>
                    <textarea id="bio" name="bio" rows="4" 
                              maxlength="500"
                              placeholder="Расскажите о себе..."><?= h($user['bio'] ?? '') ?></textarea>
                    <div class="char-counter">
                        <span id="bio-counter"><?= strlen($user['bio'] ?? '') ?></span>/500
                    </div>
                </div>
                <div class="form-group">
                    <label for="website">Сайт</label>
                    <input type="url" id="website" name="website" 
                           value="<?= h($user['website'] ?? '') ?>"
                           placeholder="https://example.com">
                    <div class="form-hint">Полный URL вашего сайта или портфолио</div>
                </div>
                <div class="form-group">
                    <label for="github">GitHub</label>
                    <input type="text" id="github" name="github" 
                           value="<?= h($user['github'] ?? '') ?>"
                           placeholder="username">
                    <div class="form-hint">Только имя пользователя (без @)</div>
                </div>
                <div class="form-group">
                    <label for="twitter">Twitter/X</label>
                    <input type="text" id="twitter" name="twitter" 
                           value="<?= h($user['twitter'] ?? '') ?>"
                           placeholder="username">
                    <div class="form-hint">Только имя пользователя (без @)</div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">💾 Сохранить изменения</button>
                    <a href="profile.php" class="btn" style="background: var(--surface); text-decoration: none;">Отмена</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        document.getElementById('bio').addEventListener('input', function() {
            document.getElementById('bio-counter').textContent = this.value.length;
        });
    </script>
</body>
</html>