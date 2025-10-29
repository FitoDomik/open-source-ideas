<?php
require_once 'config.php';
require_once 'functions.php';
$ideaId = $_GET['id'] ?? 0;
$db = getDB();
$stmt = $db->prepare("
    SELECT i.*, u.username 
    FROM ideas i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id = ?
");
$stmt->execute([$ideaId]);
$idea = $stmt->fetch();
if (!$idea) {
    header('Location: index.php');
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $action = $_POST['action'] ?? 'add_response';
    if ($action === 'add_comment') {
        $commentText = trim($_POST['comment_text'] ?? '');
        if (!empty($commentText)) {
            addComment($ideaId, $_SESSION['user_id'], $commentText);
            checkAndAwardBadges($_SESSION['user_id']);
            header('Location: idea.php?id=' . $ideaId . '&comment=success');
            exit;
        }
    } elseif ($action === 'add_response' || !isset($_POST['action'])) {
        $responseText = trim($_POST['response_text'] ?? '');
        $githubLink = trim($_POST['github_link'] ?? '');
        if (empty($responseText) && empty($githubLink)) {
            $error = 'Заполните текст ответа или укажите ссылку на GitHub';
        } else {
            try {
                $imageName = null;
                if (isset($_FILES['response_image']) && $_FILES['response_image']['error'] === UPLOAD_ERR_OK) {
                    $imageName = uploadImage($_FILES['response_image']);
                }
                $stmt = $db->prepare("
                    INSERT INTO responses (idea_id, user_id, text, github_link, image) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $ideaId,
                    $_SESSION['user_id'],
                    $responseText,
                    $githubLink,
                    $imageName
                ]);
                checkAndAwardBadges($_SESSION['user_id']);
                header('Location: idea.php?id=' . $ideaId . '&success=1');
                exit;
            } catch (Exception $e) {
                $error = 'Ошибка: ' . $e->getMessage();
            }
        }
    }
}
$stmt = $db->prepare("
    SELECT r.*, u.username 
    FROM responses r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.idea_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$ideaId]);
$responses = $stmt->fetchAll();
$likesCount = getLikesCount($ideaId);
$userLiked = hasUserLiked($ideaId, $_SESSION['user_id'] ?? null);
$comments = getComments($ideaId);
$isAuthor = isLoggedIn() && $_SESSION['user_id'] == $idea['user_id'];
if (isset($_GET['success'])) {
    $success = 'Ваш ответ успешно добавлен!';
}
if (isset($_GET['comment'])) {
    $success = 'Комментарий добавлен!';
}
if (!isset($idea['status']) || empty($idea['status'])) {
    $idea['status'] = 'new';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($idea['title']) ?> - Платформа идей</title>
    <?php include 'includes_head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="idea-detail">
            <div class="breadcrumb">
                <a href="index.php">← Назад к списку идей</a>
            </div>
            <h1><?= h($idea['title']) ?></h1>
            <div class="idea-meta">
                <span>👤 Автор: <strong><a href="profile.php?id=<?= $idea['user_id'] ?>" style="color: inherit;"><?= h($idea['username']) ?></a></strong></span>
                <span>📅 Дата: <?= date('d.m.Y H:i', strtotime($idea['created_at'])) ?></span>
                <span>💬 Ответов: <?= count($responses) ?></span>
                <span>💬 Комментариев: <?= count($comments) ?></span>
            </div>
            <div style="display: flex; gap: 15px; align-items: center; margin: 20px 0; flex-wrap: wrap;">
                <?php if (isLoggedIn()): ?>
                    <button class="like-button <?= $userLiked ? 'liked' : '' ?>" 
                            onclick="toggleLike(<?= $ideaId ?>, this)" 
                            id="like-btn-<?= $ideaId ?>">
                        <span><?= $userLiked ? '❤️' : '🤍' ?></span>
                        <span class="like-count"><?= $likesCount ?></span>
                    </button>
                <?php else: ?>
                    <a href="login.php" class="like-button">
                        <span>🤍</span>
                        <span><?= $likesCount ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($isAuthor): ?>
                    <select class="status-selector" onchange="changeStatus(<?= $ideaId ?>, this.value)" style="padding: 8px; border-radius: 8px;">
                        <option value="new" <?= ($idea['status'] ?? 'new') == 'new' ? 'selected' : '' ?>>🆕 Новая</option>
                        <option value="in_progress" <?= ($idea['status'] ?? 'new') == 'in_progress' ? 'selected' : '' ?>>🔨 В разработке</option>
                        <option value="completed" <?= ($idea['status'] ?? 'new') == 'completed' ? 'selected' : '' ?>>✅ Завершена</option>
                        <option value="abandoned" <?= ($idea['status'] ?? 'new') == 'abandoned' ? 'selected' : '' ?>>⏸ Заброшена</option>
                    </select>
                <?php else: ?>
                    <?php $status = $idea['status'] ?? 'new'; ?>
                    <span class="status-badge status-<?= $status ?>">
                        <?= getStatusName($status) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if ($idea['tags']): ?>
                <div class="idea-tags">
                    <?php foreach (explode(',', $idea['tags']) as $tag): ?>
                        <a href="index.php?tag=<?= urlencode(trim($tag)) ?>" class="tag">
                            <?= h(trim($tag)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($idea['image']): ?>
                <div class="idea-detail-image">
                    <img src="upload/<?= h($idea['image']) ?>" alt="<?= h($idea['title']) ?>">
                </div>
            <?php endif; ?>
            <div class="idea-detail-description">
                <?= nl2br(h($idea['description'])) ?>
            </div>
        </div>
        <div class="responses-section">
            <h2>💻 Ответы разработчиков (<?= count($responses) ?>)</h2>
            <?php if (isLoggedIn()): ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= h($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= h($error) ?></div>
                <?php endif; ?>
                <div class="response-form">
                    <h3>Добавить ответ</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="response_text">Ваш ответ</label>
                            <textarea id="response_text" name="response_text" rows="5"
                                      placeholder="Расскажите, как вы реализовали эту идею или что предлагаете..."><?= h($_POST['response_text'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="github_link">Ссылка на GitHub/репозиторий</label>
                            <input type="url" id="github_link" name="github_link" 
                                   value="<?= h($_POST['github_link'] ?? '') ?>"
                                   placeholder="https://github.com/username/project">
                        </div>
                        <div class="form-group">
                            <label for="response_image">Изображение (скриншот)</label>
                            <input type="file" id="response_image" name="response_image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить ответ</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <a href="login.php">Войдите</a> или <a href="register.php">зарегистрируйтесь</a>, 
                    чтобы оставить ответ на эту идею
                </div>
            <?php endif; ?>
            <?php if (empty($responses)): ?>
                <div class="empty-state">
                    <p>Пока нет ответов. Будьте первым!</p>
                </div>
            <?php else: ?>
                <div class="responses-list">
                    <?php foreach ($responses as $response): ?>
                        <div class="response-card">
                            <div class="response-header">
                                <strong>👤 <?= h($response['username']) ?></strong>
                                <span class="response-date">
                                    <?= date('d.m.Y H:i', strtotime($response['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($response['text']): ?>
                                <div class="response-text">
                                    <?= nl2br(h($response['text'])) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($response['github_link']): ?>
                                <div class="response-github">
                                    🔗 <a href="<?= h($response['github_link']) ?>" 
                                          target="_blank" rel="noopener">
                                        <?= h($response['github_link']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($response['image']): ?>
                                <div class="response-image">
                                    <img src="upload/<?= h($response['image']) ?>" 
                                         alt="Скриншот от <?= h($response['username']) ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="comments-section">
            <h2>💬 Комментарии (<?= count($comments) ?>)</h2>
            <?php if (isLoggedIn()): ?>
                <div class="response-form">
                    <h3>Добавить комментарий</h3>
                    <form id="comment-form" method="POST" action="">
                        <input type="hidden" name="action" value="add_comment">
                        <div class="form-group">
                            <textarea id="comment_text" name="comment_text" rows="3"
                                      placeholder="Ваш комментарий..." required><?= h($_POST['comment_text'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить комментарий</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <a href="login.php">Войдите</a> или <a href="register.php">зарегистрируйтесь</a>, 
                    чтобы оставить комментарий
                </div>
            <?php endif; ?>
            <?php if (empty($comments)): ?>
                <div class="empty-state">
                    <p>Пока нет комментариев. Будьте первым!</p>
                </div>
            <?php else: ?>
                <div class="responses-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <img src="<?= getUserAvatar($comment['avatar'] ?? null, $comment['username']) ?>" 
                                         alt="<?= h($comment['username']) ?>" 
                                         class="comment-avatar">
                                    <strong><a href="profile.php?id=<?= $comment['user_id'] ?>"><?= h($comment['username']) ?></a></strong>
                                </div>
                                <div>
                                    <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                                    <?php if (isLoggedIn() && ($_SESSION['user_id'] == $comment['user_id'] || isAdmin())): ?>
                                        <form method="POST" action="ajax_comment.php" style="display: inline;" 
                                              onsubmit="return confirm('Удалить комментарий?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                            <button type="submit" style="background: none; border: none; color: var(--danger-color); cursor: pointer; margin-left: 10px;">✕</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(h($comment['text'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>