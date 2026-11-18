<?php
require_once __DIR__ . '/app.php';
require_post();
$username = post('username');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$next = safe_next(post('next'));
rate_limit('login-ip', 50, 900);
rate_limit('login-account', 10, 900, $username);
$errors = [];
if ($username === '' || strlen($username) > 50) $errors['username'] = 'Enter your username.';
if ($password === '' || strlen($password) > 1024) $errors['password'] = 'Enter your password.';
if ($errors) form_fail('login', $errors, 'login.php?next=' . rawurlencode($next), ['username'=>$username]);
$stmt = db()->prepare('SELECT id, username, hashed_password, role FROM users WHERE username = ? LIMIT 2');
$stmt->bind_param('s', $username); $stmt->execute(); $result = $stmt->get_result();
$user = $result->fetch_assoc();
// A fixed bcrypt hash keeps unknown-account checks comparable to password checks.
$hash = $user['hashed_password'] ?? '$2y$12$G.uNfnlFOxl1BBb9SqRWuusvgZ6uZ8V3fvbGQfGeHrXGlua92nob6';
if (!password_verify($password, $hash) || $result->num_rows !== 1) {
    form_fail('login', ['form'=>'The username or password is incorrect.'], 'login.php?next=' . rawurlencode($next), ['username'=>$username]);
}
if (password_needs_rehash($user['hashed_password'], PASSWORD_DEFAULT)) {
    $updated = password_hash($password, PASSWORD_DEFAULT); $id = (int)$user['id'];
    $stmt = db()->prepare('UPDATE users SET hashed_password = ? WHERE id = ?'); $stmt->bind_param('si', $updated, $id); $stmt->execute();
}
sign_in($user);
flash('success', 'Welcome back. You are signed in.');
redirect($user['role'] === 'admin' && $next === 'home.php' ? 'admin/adminDash.php' : $next);
