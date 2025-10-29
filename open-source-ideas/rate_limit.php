<?php
function checkRateLimit($userId, $action, $maxAttempts = 10, $periodSeconds = 60) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT action_count, last_action 
        FROM rate_limits 
        WHERE user_id = ? AND action_type = ?
    ");
    $stmt->execute([$userId, $action]);
    $limit = $stmt->fetch();
    $now = time();
    if ($limit) {
        $lastAction = strtotime($limit['last_action']);
        $timePassed = $now - $lastAction;
        if ($timePassed > $periodSeconds) {
            $stmt = $db->prepare("
                UPDATE rate_limits 
                SET action_count = 1, last_action = NOW() 
                WHERE user_id = ? AND action_type = ?
            ");
            $stmt->execute([$userId, $action]);
            return true;
        }
        if ($limit['action_count'] >= $maxAttempts) {
            $remaining = $periodSeconds - $timePassed;
            throw new Exception("Слишком много попыток. Подождите " . ceil($remaining) . " секунд.");
        }
        $stmt = $db->prepare("
            UPDATE rate_limits 
            SET action_count = action_count + 1, last_action = NOW() 
            WHERE user_id = ? AND action_type = ?
        ");
        $stmt->execute([$userId, $action]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO rate_limits (user_id, action_type, action_count, last_action) 
            VALUES (?, ?, 1, NOW())
        ");
        $stmt->execute([$userId, $action]);
    }
    return true;
}
function generateCaptcha() {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    $_SESSION['captcha'] = $code;
    return $code;
}
function verifyCaptcha($input) {
    return isset($_SESSION['captcha']) && strtoupper($input) === $_SESSION['captcha'];
}