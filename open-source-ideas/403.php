<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Доступ запрещён</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicons/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicons/apple-touch-icon.png">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        .error-content {
            max-width: 600px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: var(--danger-color);
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: var(--text-primary);
        }
        .error-description {
            color: var(--text-secondary);
            margin-bottom: 40px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">403</div>
            <h1 class="error-message">🚫 Доступ запрещён</h1>
            <p class="error-description">
                К сожалению, у вас нет прав для доступа к этой странице.
                Возможно, вам нужно войти в систему или у вас недостаточно прав.
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn btn-primary">На главную</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Назад</a>
                <a href="login.php" class="btn btn-secondary">Войти</a>
            </div>
        </div>
    </div>
</body>
</html>