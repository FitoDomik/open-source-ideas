<?php
require_once 'config.php';
require_once 'functions.php';
$userId = $_GET['id'] ?? $_SESSION['user_id'] ?? 0;
if (!$userId) {
    header('Location: login.php');
    exit;
}
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: index.php');
    exit;
}
$stats = getUserStats($userId);
$badges = getUserBadges($userId);
$stmt = $db->prepare("
    SELECT i.*, 
        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes_count,
        (SELECT COUNT(*) FROM comments WHERE idea_id = i.id) as comments_count,
        (SELECT COUNT(*) FROM responses WHERE idea_id = i.id) as responses_count
    FROM ideas i 
    WHERE i.user_id = ? 
    ORDER BY i.created_at DESC
");
$stmt->execute([$userId]);
$userIdeas = $stmt->fetchAll();
$stmt = $db->prepare("
    SELECT r.*, i.title as idea_title, i.id as idea_id
    FROM responses r
    JOIN ideas i ON r.idea_id = i.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$userResponses = $stmt->fetchAll();
$isOwnProfile = isLoggedIn() && $_SESSION['user_id'] == $userId;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($user['username']) ?> - Профиль пользователя</title>
    <?php include 'includes_head.php'; ?>
    <style>
        .profile-header {
            background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 50%, #7c3aed 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.35) 100%);
            border-radius: 12px;
            z-index: 0;
        }
        .profile-header > * {
            position: relative;
            z-index: 1;
        }
        .profile-header h1 {
            color: #ffffff !important;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.6), 0 0 20px rgba(0, 0, 0, 0.3);
            font-weight: 700;
            font-size: 32px;
        }
        .profile-header p {
            color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            font-size: 16px;
        }
        .profile-info {
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 42px;
            font-weight: 800;
            color: #ffffff !important;
            text-shadow: 0 3px 8px rgba(0, 0, 0, 0.7), 0 0 30px rgba(255, 255, 255, 0.3);
            letter-spacing: -1px;
        }
        .stat-label {
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7), 0 1px 3px rgba(0, 0, 0, 0.5);
            opacity: 1 !important;
            margin-top: 5px;
        }
        .badges-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .badge-item {
            background: rgba(255, 255, 255, 0.98) !important;
            color: #1e293b !important;
            padding: 8px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.6);
            font-weight: 600;
        }
        [data-theme="dark"] .badge-item {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.4);
        }
        .tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            overflow-x: auto;
            overflow-y: hidden;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .tab-nav::-webkit-scrollbar {
            height: 4px;
        }
        .tab-nav::-webkit-scrollbar-track {
            background: var(--background);
        }
        .tab-nav::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 2px;
        }
        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-secondary);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            flex-shrink: 0;
        }
        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .achievement-card {
            transition: all 0.3s ease;
        }
        .achievement-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .achievement-card.earned {
            animation: glow 2s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from {
                box-shadow: 0 0 5px var(--primary-color);
            }
            to {
                box-shadow: 0 0 20px var(--primary-color), 0 0 30px var(--primary-color);
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="profile-header">
            <div class="profile-info">
                <img src="<?= getUserAvatar($user['avatar'], $user['username']) ?>" 
                     alt="<?= h($user['username']) ?>" 
                     class="profile-avatar">
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                        <h1><?= h($user['username']) ?></h1>
                        <div style="display: flex; gap: 10px;">
                            <?php if ($isOwnProfile): ?>
                                <a href="edit_profile.php" class="btn" style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.5); text-decoration: none; padding: 8px 16px; font-size: 14px;">
                                    ⚙️ Редактировать
                                </a>
                            <?php elseif (isLoggedIn() && $_SESSION['user_id'] != $userId): 
                                $stmt = $db->prepare("SELECT id FROM subscriptions WHERE follower_id = ? AND following_id = ?");
                                $stmt->execute([$_SESSION['user_id'], $userId]);
                                $isSubscribed = $stmt->fetch() !== false;
                            ?>
                                <button class="subscribe-btn <?= $isSubscribed ? 'subscribed' : '' ?>" 
                                        onclick="toggleSubscribe(<?= $userId ?>, this)"
                                        style="background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid rgba(255, 255, 255, 0.5);">
                                    <?= $isSubscribed ? '🔔 Подписка' : '➕ Подписаться' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($user['bio']): ?>
                        <p style="margin-bottom: 15px;"><?= h($user['bio']) ?></p>
                    <?php elseif ($isOwnProfile): ?>
                        <p style="opacity: 0.7; font-style: italic; margin-bottom: 15px;">
                            Добавьте описание профиля →
                            <a href="edit_profile.php" style="color: white; text-decoration: underline;">Редактировать</a>
                        </p>
                    <?php endif; ?>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <?php if ($user['website']): ?>
                            <a href="<?= h($user['website']) ?>" target="_blank" style="color: #ffffff; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5); font-weight: 500;">
                                🌐 Сайт
                            </a>
                        <?php endif; ?>
                        <?php if ($user['github']): ?>
                            <a href="https://github.com/<?= h($user['github']) ?>" target="_blank" style="color: #ffffff; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5); font-weight: 500;">
                                💻 GitHub
                            </a>
                        <?php endif; ?>
                        <?php if ($user['twitter']): ?>
                            <a href="https://twitter.com/<?= h($user['twitter']) ?>" target="_blank" style="color: #ffffff; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5); font-weight: 500;">
                                🐦 Twitter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['ideas_count'] ?></div>
                    <div class="stat-label">Идей</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['responses_count'] ?></div>
                    <div class="stat-label">Ответов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $stats['likes_received'] ?></div>
                    <div class="stat-label">Лайков</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= count($badges) ?></div>
                    <div class="stat-label">Достижений</div>
                </div>
            </div>
        </div>
        <div class="tab-nav">
            <button class="tab-btn active" onclick="showTab('ideas')">💡 Идеи (<?= count($userIdeas) ?>)</button>
            <?php if ($isOwnProfile): 
                $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
                $stmt->execute([$userId]);
                $favoritesCount = $stmt->fetchColumn();
            ?>
                <button class="tab-btn" onclick="showTab('favorites')">⭐ Избранное (<?= $favoritesCount ?>)</button>
            <?php endif; ?>
            <button class="tab-btn" onclick="showTab('responses')">💻 Ответы (<?= count($userResponses) ?>)</button>
            <?php if ($isOwnProfile): 
                $stmt = $db->prepare("SELECT COUNT(*) FROM subscriptions WHERE follower_id = ?");
                $stmt->execute([$userId]);
                $subscriptionsCount = $stmt->fetchColumn();
            ?>
                <button class="tab-btn" onclick="showTab('subscriptions')">🔔 Подписки (<?= $subscriptionsCount ?>)</button>
            <?php endif; ?>
            <button class="tab-btn" onclick="showTab('achievements')">🏆 Достижения (<?= count($badges) ?>)</button>
        </div>
        <div class="tab-content active" id="tab-ideas">
            <?php if (empty($userIdeas)): ?>
                <div class="empty-state">
                    <p>Пока нет опубликованных идей</p>
                </div>
            <?php else: ?>
                <div class="ideas-grid">
                    <?php foreach ($userIdeas as $idea): ?>
                        <div class="idea-card">
                            <?php if ($idea['image']): ?>
                                <div class="idea-image">
                                    <img src="upload/<?= h($idea['image']) ?>" alt="<?= h($idea['title']) ?>" loading="lazy">
                                </div>
                            <?php endif; ?>
                            <div class="idea-content">
                                <h3><?= h($idea['title']) ?></h3>
                                <p class="idea-description"><?= h(mb_substr($idea['description'], 0, 150)) ?>...</p>
                                <div style="margin: 15px 0; display: flex; gap: 15px; font-size: 14px;">
                                    <span>⭐ <?= $idea['likes_count'] ?></span>
                                    <span>💬 <?= $idea['comments_count'] ?></span>
                                    <span>💻 <?= $idea['responses_count'] ?></span>
                                </div>
                                <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary btn-block">Подробнее</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($isOwnProfile): ?>
            <div class="tab-content" id="tab-favorites">
                <?php
                $stmt = $db->prepare("
                    SELECT i.*, u.username,
                        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes_count
                    FROM favorites f
                    JOIN ideas i ON f.idea_id = i.id
                    JOIN users u ON i.user_id = u.id
                    WHERE f.user_id = ?
                    ORDER BY f.created_at DESC
                ");
                $stmt->execute([$userId]);
                $favorites = $stmt->fetchAll();
                ?>
                <?php if (empty($favorites)): ?>
                    <div class="empty-state">
                        <p>У вас пока нет избранных идей</p>
                    </div>
                <?php else: ?>
                    <div class="ideas-grid">
                        <?php foreach ($favorites as $idea): ?>
                            <div class="idea-card">
                                <?php if ($idea['image']): ?>
                                    <div class="idea-image">
                                        <img src="upload/<?= h($idea['image']) ?>" alt="<?= h($idea['title']) ?>" loading="lazy">
                                    </div>
                                <?php endif; ?>
                                <div class="idea-content">
                                    <h3><?= h($idea['title']) ?></h3>
                                    <p class="idea-description"><?= h(mb_substr($idea['description'], 0, 150)) ?>...</p>
                                    <div style="margin: 15px 0; display: flex; gap: 15px; font-size: 14px;">
                                        <span>⭐ <?= $idea['likes_count'] ?></span>
                                    </div>
                                    <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary btn-block">Подробнее</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="tab-content" id="tab-subscriptions">
                <?php
                $stmt = $db->prepare("
                    SELECT i.*, u.username,
                        (SELECT COUNT(*) FROM idea_likes WHERE idea_id = i.id) as likes_count
                    FROM subscriptions s
                    JOIN ideas i ON s.following_id = i.user_id
                    JOIN users u ON i.user_id = u.id
                    WHERE s.follower_id = ?
                    ORDER BY i.created_at DESC
                    LIMIT 50
                ");
                $stmt->execute([$userId]);
                $subscriptionIdeas = $stmt->fetchAll();
                ?>
                <?php if (empty($subscriptionIdeas)): ?>
                    <div class="empty-state">
                        <p>Вы не подписаны ни на одного автора</p>
                        <p style="margin-top: 10px;">Подпишитесь на интересных авторов, чтобы видеть их идеи здесь!</p>
                    </div>
                <?php else: ?>
                    <div class="ideas-grid">
                        <?php foreach ($subscriptionIdeas as $idea): ?>
                            <div class="idea-card">
                                <?php if ($idea['image']): ?>
                                    <div class="idea-image">
                                        <img src="upload/<?= h($idea['image']) ?>" alt="<?= h($idea['title']) ?>" loading="lazy">
                                    </div>
                                <?php endif; ?>
                                <div class="idea-content">
                                    <h3><?= h($idea['title']) ?></h3>
                                    <p class="idea-description"><?= h(mb_substr($idea['description'], 0, 150)) ?>...</p>
                                    <div style="margin: 15px 0; display: flex; gap: 15px; font-size: 14px;">
                                        <span>👤 <?= h($idea['username']) ?></span>
                                        <span>⭐ <?= $idea['likes_count'] ?></span>
                                    </div>
                                    <a href="idea.php?id=<?= $idea['id'] ?>" class="btn btn-primary btn-block">Подробнее</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="tab-content" id="tab-responses">
            <?php if (empty($userResponses)): ?>
                <div class="empty-state">
                    <p>Пока нет ответов</p>
                </div>
            <?php else: ?>
                <?php foreach ($userResponses as $response): ?>
                    <div class="response-card" style="margin-bottom: 20px;">
                        <h4>
                            <a href="idea.php?id=<?= $response['idea_id'] ?>">
                                <?= h($response['idea_title']) ?>
                            </a>
                        </h4>
                        <p><?= nl2br(h($response['text'])) ?></p>
                        <?php if ($response['github_link']): ?>
                            <p>🔗 <a href="<?= h($response['github_link']) ?>" target="_blank">GitHub</a></p>
                        <?php endif; ?>
                        <small>📅 <?= date('d.m.Y H:i', strtotime($response['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="tab-content" id="tab-achievements">
            <div style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 20px;">🏆 Мои достижения</h2>
                <?php
                $allBadges = $db->query("SELECT * FROM badges ORDER BY id ASC")->fetchAll();
                $earnedBadgeIds = array_column($badges, 'badge_id');
                ?>
                <div style="display: grid; gap: 20px;">
                    <?php foreach ($allBadges as $badge): 
                        $isEarned = in_array($badge['id'], $earnedBadgeIds);
                        $progress = getBadgeProgress($userId, $badge['criteria'], $badge['id']);
                    ?>
                        <div class="achievement-card <?= $isEarned ? 'earned' : 'locked' ?>" style="
                            background: var(--surface);
                            padding: 20px;
                            border-radius: 12px;
                            border: 2px solid <?= $isEarned ? 'var(--primary-color)' : 'var(--border-color)' ?>;
                            opacity: <?= $isEarned ? '1' : '0.6' ?>;
                            position: relative;
                            overflow: hidden;
                        ">
                            <?php if ($isEarned): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background: var(--success-color); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    ✓ ПОЛУЧЕНО
                                </div>
                            <?php endif; ?>
                            <div style="display: flex; align-items: start; gap: 20px;">
                                <div style="font-size: 48px; filter: <?= $isEarned ? 'none' : 'grayscale(100%)' ?>;">
                                    <?= $badge['icon'] ?>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 10px 0; color: <?= $isEarned ? 'var(--primary-color)' : 'var(--text-secondary)' ?>;">
                                        <?= h($badge['name']) ?>
                                    </h3>
                                    <p style="color: var(--text-secondary); margin: 0 0 15px 0;">
                                        <?= h($badge['description']) ?>
                                    </p>
                                    <?php if (!$isEarned && $progress): ?>
                                        <div class="progress-bar" style="
                                            background: var(--border-color);
                                            height: 8px;
                                            border-radius: 4px;
                                            overflow: hidden;
                                            margin-top: 10px;
                                        ">
                                            <div style="
                                                background: var(--primary-color);
                                                height: 100%;
                                                width: <?= min(100, ($progress['current'] / $progress['required']) * 100) ?>%;
                                                transition: width 0.3s;
                                            "></div>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; margin-top: 5px; font-size: 12px; color: var(--text-secondary);">
                                            <span><?= $progress['current'] ?> / <?= $progress['required'] ?></span>
                                            <span><?= round(($progress['current'] / $progress['required']) * 100) ?>%</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($isEarned): 
                                        $earnedDate = null;
                                        foreach ($badges as $b) {
                                            if ($b['badge_id'] == $badge['id']) {
                                                $earnedDate = $b['earned_at'];
                                                break;
                                            }
                                        }
                                    ?>
                                        <div style="margin-top: 10px; font-size: 13px; color: var(--text-secondary);">
                                            📅 Получено: <?= date('d.m.Y', strtotime($earnedDate)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($allBadges)): ?>
                    <div class="empty-state">
                        <p>Пока нет доступных достижений</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        }
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        });
    </script>
</body>
</html>