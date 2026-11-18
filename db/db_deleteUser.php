<?php
require_once __DIR__ . '/app.php';
require_post(); $user=require_user();
$password=is_string($_POST['current_password']??null) ? $_POST['current_password'] : '';
$stmt=db()->prepare('SELECT hashed_password FROM users WHERE id=?'); $stmt->bind_param('i',$user['id']); $stmt->execute();
if (post('confirmation')!=='DELETE' || !password_verify($password,$stmt->get_result()->fetch_assoc()['hashed_password'])) {
    flash('error','To delete your account, enter your current password and type DELETE.'); redirect('profile.php#delete-account');
}
$connection=db();$connection->begin_transaction();
try {
    account_write_lock($connection);
    if ($user['role']==='admin' && (int)$connection->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0] <= 1) {
        $connection->rollback(); flash('error','Create another administrator before deleting the last administrator account.');redirect('profile.php');
    }
    // Explicit child deletion also supports older installations without cascading foreign keys.
    $stmt=$connection->prepare('DELETE oi FROM order_items oi JOIN orders o ON o.order_id=oi.order_id WHERE o.user_id=?'); $stmt->bind_param('i',$user['id']);$stmt->execute();
    foreach (['reviews','orders'] as $table) { $stmt=$connection->prepare('DELETE FROM '.$table.' WHERE user_id=?');$stmt->bind_param('i',$user['id']);$stmt->execute(); }
    $stmt=$connection->prepare('DELETE FROM users WHERE id=?');$stmt->bind_param('i',$user['id']);$stmt->execute();$connection->commit();
} catch (Throwable $error) { $connection->rollback();throw $error; }
destroy_login();redirect('home.php');
