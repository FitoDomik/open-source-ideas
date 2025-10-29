-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Окт 29 2025 г., 20:50
-- Версия сервера: 8.0.25-15
-- Версия PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u3307679_open-source-ideas`
--

-- --------------------------------------------------------

--
-- Структура таблицы `badges`
--

CREATE TABLE `badges` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Критерий получения (например: ideas_count_5)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `badges`
--

INSERT INTO `badges` (`id`, `name`, `description`, `icon`, `criteria`, `created_at`) VALUES
(1, '🎯 Первая идея', 'Опубликовал свою первую идею', '🎯', 'ideas_count_1', '2025-10-29 17:08:09'),
(2, '🚀 Активный автор', 'Опубликовал 5 идей', '🚀', 'ideas_count_5', '2025-10-29 17:08:09'),
(3, '💎 Мастер идей', 'Опубликовал 10 идей', '💎', 'ideas_count_10', '2025-10-29 17:08:09'),
(4, '💬 Первый ответ', 'Оставил первый ответ разработчика', '💬', 'responses_count_1', '2025-10-29 17:08:09'),
(5, '🏆 Активный разработчик', 'Ответил на 5 идей', '🏆', 'responses_count_5', '2025-10-29 17:08:09'),
(6, '⭐ Профессионал', 'Ответил на 10 идей', '⭐', 'responses_count_10', '2025-10-29 17:08:09'),
(7, '🔥 Популярный', 'Получил 10 лайков на идею', '🔥', 'likes_received_10', '2025-10-29 17:08:09'),
(8, '👑 Легенда', 'Получил 50 лайков на свои идеи', '👑', 'likes_received_50', '2025-10-29 17:08:09'),
(9, '💭 Комментатор', 'Оставил 10 комментариев', '💭', 'comments_count_10', '2025-10-29 17:08:09'),
(10, '🎨 Креатор', 'Первая идея со статусом \"Завершено\"', '🎨', 'completed_idea_1', '2025-10-29 17:08:09');

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `idea_id` int NOT NULL,
  `user_id` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `favorites`
--

CREATE TABLE `favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `idea_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `ideas`
--

CREATE TABLE `ideas` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('new','in_progress','completed','abandoned') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `views` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `idea_files`
--

CREATE TABLE `idea_files` (
  `id` int NOT NULL,
  `idea_id` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `idea_likes`
--

CREATE TABLE `idea_likes` (
  `id` int NOT NULL,
  `idea_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_count` int DEFAULT '1',
  `last_action` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `responses`
--

CREATE TABLE `responses` (
  `id` int NOT NULL,
  `idea_id` int NOT NULL,
  `user_id` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `github_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `follower_id` int NOT NULL,
  `following_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`, `created_at`, `avatar`, `bio`, `website`, `github`, `twitter`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-29 15:47:29', NULL, NULL, NULL, NULL, NULL),
(3, 'Slavik10', '$2y$10$D7kx88w9ILNRF5j4TtE5JOatRPenno0QwZVFRZJlUc1a.Bue06ldK', 1, '2025-10-29 15:50:16', 'avatar_3_1761759464.png', 'Слава, создатель сайта', 'https://fitodomik.online/', 'https://github.com/FitoDomik', ''),
(4, 'Legenda', '$2y$10$sSUS7HaXZp2K3oQeqtlsdunY4CaAoja2ACU9jC9fCc8BGwyO4V4Yi', 0, '2025-10-29 17:15:03', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `badge_id` int NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_id`, `earned_at`) VALUES
(1, 3, 8, '2025-10-29 17:42:06');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_idea_id` (`idea_id`),
  ADD KEY `idx_created_at` (`created_at` DESC);

--
-- Индексы таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`idea_id`),
  ADD KEY `idx_favorites_user` (`user_id`),
  ADD KEY `idx_favorites_idea` (`idea_id`);

--
-- Индексы таблицы `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at` DESC),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_ideas_created` (`created_at` DESC),
  ADD KEY `idx_ideas_status` (`status`);
ALTER TABLE `ideas` ADD FULLTEXT KEY `ft_search` (`title`,`description`,`tags`);

--
-- Индексы таблицы `idea_files`
--
ALTER TABLE `idea_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_idea_files_idea` (`idea_id`);

--
-- Индексы таблицы `idea_likes`
--
ALTER TABLE `idea_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`idea_id`,`user_id`),
  ADD KEY `idx_idea_id` (`idea_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Индексы таблицы `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rate_limit` (`user_id`,`action_type`);

--
-- Индексы таблицы `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_idea_id` (`idea_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Индексы таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subscription` (`follower_id`,`following_id`),
  ADD KEY `idx_subscriptions_follower` (`follower_id`),
  ADD KEY `idx_subscriptions_following` (`following_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Индексы таблицы `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `idea_files`
--
ALTER TABLE `idea_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `idea_likes`
--
ALTER TABLE `idea_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `responses`
--
ALTER TABLE `responses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `ideas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `idea_files`
--
ALTER TABLE `idea_files`
  ADD CONSTRAINT `idea_files_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `idea_likes`
--
ALTER TABLE `idea_likes`
  ADD CONSTRAINT `idea_likes_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `idea_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD CONSTRAINT `rate_limits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
