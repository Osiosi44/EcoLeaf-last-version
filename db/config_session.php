<?php
if (session_status() === PHP_SESSION_ACTIVE) {
    return;
}
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (getenv('SESSION_SECURE') === '1');
session_set_cookie_params([
    'lifetime' => 1800,
    'path' => '/',
    'secure' => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > 1800) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] >= 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
