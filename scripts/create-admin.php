<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/../db/db_con.php';
$username = trim((string) getenv('ADMIN_USERNAME'));
$email = trim((string) getenv('ADMIN_EMAIL'));
$password = (string) getenv('ADMIN_PASSWORD');
if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Set ADMIN_USERNAME, ADMIN_EMAIL, and ADMIN_PASSWORD (at least 12 characters).\n");
    exit(1);
}
$check = $con->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
$check->bind_param('ss', $username, $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    fwrite(STDERR, "That username or email already exists.\n");
    exit(1);
}
$name = 'Demo Administrator';
$phone = '';
$address = '';
$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';
$stmt = $con->prepare('INSERT INTO users (full_name, username, phone_number, addres, email, hashed_password, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('sssssss', $name, $username, $phone, $address, $email, $hash, $role);
$stmt->execute();
echo "Administrator created.\n";
