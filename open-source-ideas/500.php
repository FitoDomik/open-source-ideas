<?php http_response_code(500); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Внутренняя ошибка сервера</title>
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
            color: #ef4444;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: #0f172a;
        }
        .error-description {
            color: #64748b;
            margin-bottom: 40px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-code">500</div>
            <h1 class="error-message">⚠️ Внутренняя ошибка сервера</h1>
            <p class="error-description">
                Упс! Что-то пошло не так на нашей стороне.
                Мы уже работаем над исправлением проблемы.
                Пожалуйста, попробуйте обновить страницу через некоторое время.
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php" class="btn btn-primary">На главную</a>
                <a href="javascript:location.reload()" class="btn btn-secondary">Обновить</a>
            </div>
        </div>
    </div>
</body>
</html>