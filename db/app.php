<?php
declare(strict_types=1);

require_once __DIR__ . '/config_session.php';
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Addis_Ababa');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store');

set_exception_handler(function (Throwable $error): void {
    error_log('EcoLeaf: ' . $error);
    http_response_code(500);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Something went wrong | EcoLeaf</title><link rel="stylesheet" href="/styles.css"><main class="container page-heading"><h1>We could not finish that request.</h1><p>Please try again. If this continues, contact your site administrator.</p><a class="button" href="/home.php">Back to home</a></main></html>';
});

function db(): mysqli {
    static $connection;
    if (!$connection) {
        require __DIR__ . '/db_con.php';
        $connection = $con;
    }
    return $connection;
}
function h(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function text_length(string $value): int {
    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}
function url(string $path = ''): string { return rtrim(getenv('APP_BASE_PATH') ?: '', '/') . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path), true, 303); exit; }
function post(string $key, string $default = ''): string { return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : $default; }
function query(string $key, string $default = ''): string { return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : $default; }
function wants_json(): bool { return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'); }
function json_response(array $body, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_THROW_ON_ERROR); exit;
}
function reject(int $status, string $message): never {
    if (wants_json()) json_response(['error' => $message], $status);
    http_response_code($status); header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Request could not be completed | EcoLeaf</title><link rel="stylesheet" href="' . h(url('styles.css')) . '"><main class="container page-heading"><h1>Request could not be completed.</h1><p>' . h($message) . '</p><a class="button" href="' . h(url('home.php')) . '">Back to home</a></main></html>'; exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">'; }
function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { header('Allow: POST'); reject(405, 'Use the form to submit this action.'); }
    $token = post('csrf_token', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$token || !hash_equals(csrf_token(), $token)) reject(403, 'Your form has expired. Reload the page and try again.');
}
function current_user(): ?array {
    static $loaded = false, $user = null;
    if (!$loaded) {
        $loaded = true;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare('SELECT id, full_name, username, email, phone_number, addres, role, created_at FROM users WHERE id = ?');
            $stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute(); $user = $stmt->get_result()->fetch_assoc();
            if (!$user) unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_role']);
        }
    }
    return $user;
}
function require_user(): array {
    $user = current_user();
    if (!$user) {
        if (wants_json()) reject(401, 'Sign in to continue.');
        flash('info', 'Sign in to continue.'); redirect('login.php?next=' . rawurlencode(safe_next(basename($_SERVER['SCRIPT_NAME'] ?? ''))));
    }
    return $user;
}
function require_admin(): array {
    $user = require_user(); if ($user['role'] !== 'admin') reject(403, 'This page is available to administrators only.'); return $user;
}
function safe_next(string $next): string { return in_array($next, ['booking.php', 'my_orders.php', 'profile.php', 'package.php'], true) ? $next : 'home.php'; }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function form_fail(string $form, array $errors, string $destination, array $values = []): never {
    unset($values['password'], $values['current_password'], $values['new_password'], $values['csrf_token']);
    $_SESSION['form_state'] = ['form' => $form, 'errors' => $errors, 'values' => $values]; redirect($destination);
}
function form_state(string $form): array {
    if (($_SESSION['form_state']['form'] ?? '') !== $form) return ['errors' => [], 'values' => []];
    $state = $_SESSION['form_state']; unset($_SESSION['form_state']); return $state;
}
function error_text(array $errors, string $key): string { return isset($errors[$key]) ? '<p class="field-error" id="error-' . h($key) . '">' . h($errors[$key]) . '</p>' : ''; }
function cents(string $amount): int {
    if (!preg_match('/^\d{1,6}(?:\.\d{1,2})?$/D', $amount)) throw new InvalidArgumentException('Enter a valid price with at most two decimal places.');
    [$whole, $fraction] = array_pad(explode('.', $amount), 2, ''); return (int)$whole * 100 + (int)str_pad($fraction, 2, '0');
}
function money(mixed $amount): string { return '$' . number_format((float)$amount, 2); }
function status_label(string $status): string { return ['pending'=>'Scheduled','in_progress'=>'In care','complete'=>'Completed','cancelled'=>'Cancelled'][$status] ?? 'Unknown'; }
function sign_in(array $user): void {
    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['user_id'] = (int)$user['id']; $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_role'] = $user['role']; $_SESSION['last_activity'] = time(); $_SESSION['last_regeneration'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function destroy_login(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>$p['path'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']); }
    session_destroy();
}
function rate_limit(string $scope, int $limit, int $seconds, string $identity = ''): void {
    $key = hash('sha256', $scope . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local') . '|' . strtolower($identity));
    $connection = db();
    $stmt = $connection->prepare('INSERT INTO security_limits (bucket_key, attempts, window_start) VALUES (?, 1, UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts = IF(window_start < UNIX_TIMESTAMP() - ?, 1, attempts + 1), window_start = IF(window_start < UNIX_TIMESTAMP() - ?, UNIX_TIMESTAMP(), window_start)');
    $stmt->bind_param('sii', $key, $seconds, $seconds); $stmt->execute();
    $stmt = $connection->prepare('SELECT attempts FROM security_limits WHERE bucket_key = ?'); $stmt->bind_param('s', $key); $stmt->execute();
    if ((int)$stmt->get_result()->fetch_assoc()['attempts'] > $limit) { header('Retry-After: ' . $seconds); reject(429, 'Too many attempts. Please wait before trying again.'); }
    // A bounded cleanup keeps this table small without requiring a scheduled task.
    $connection->query('DELETE FROM security_limits WHERE window_start < UNIX_TIMESTAMP() - 86400 LIMIT 100');
}
function account_write_lock(mysqli $connection): void {
    $key = hash('sha256', 'ecoleaf-account-write-lock');
    $connection->query("INSERT IGNORE INTO security_limits (bucket_key, attempts, window_start) VALUES ('" . $key . "', 0, 2147483647)");
    $connection->query("SELECT bucket_key FROM security_limits WHERE bucket_key = '" . $key . "' FOR UPDATE");
}
function account_errors(string $name, string $username, string $email, string $phone, string $address, string $password, bool $new = true): array {
    $errors = [];
    if ($name === '' || text_length($name) > 100) $errors['name'] = 'Enter your name (up to 100 characters).';
    if ($new && !preg_match('/^[a-zA-Z0-9_-]{3,32}$/D', $username)) $errors['username'] = 'Use 3–32 letters, numbers, underscores, or hyphens.';
    if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (!preg_match('/^[+0-9() .-]{7,15}$/D', $phone)) $errors['phone'] = 'Enter a phone number using 7–15 characters.';
    if (text_length($address) > 255) $errors['address'] = 'Use an address shorter than 256 characters.';
    if (($new || $password !== '') && (strlen($password) < 10 || strlen($password) > 72)) $errors['password'] = 'Use a password between 10 and 72 bytes.';
    return $errors;
}
function duplicate_account(mysqli $connection, string $username, string $email, int $except = 0): ?string {
    $stmt = $connection->prepare('SELECT username, email FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
    $stmt->bind_param('ssi', $username, $email, $except); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
    return $row ? (strcasecmp($row['username'], $username) === 0 ? 'username' : 'email') : null;
}
function service_catalog(): array {
    $services = db()->query('SELECT s.*, (SELECT MIN(price) FROM service_options WHERE service_id = s.service_id) AS option_min FROM services s ORDER BY s.service_id')->fetch_all(MYSQLI_ASSOC);
    $options = db()->query('SELECT option_id, service_id, option_name, price FROM service_options ORDER BY option_id')->fetch_all(MYSQLI_ASSOC);
    foreach ($services as &$service) $service['options'] = array_values(array_filter($options, fn($option) => (int)$option['service_id'] === (int)$service['service_id']));
    unset($service); return $services;
}
