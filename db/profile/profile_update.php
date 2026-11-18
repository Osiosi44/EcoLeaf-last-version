<?php
require_once __DIR__ . '/../app.php';
require_post(); $user=require_user();
$name=post('name'); $email=strtolower(post('email')); $phone=post('phone'); $address=post('address');
$password=is_string($_POST['password']??null) ? $_POST['password'] : '';
$current=is_string($_POST['current_password']??null) ? $_POST['current_password'] : '';
$errors=account_errors($name,$user['username'],$email,$phone,$address,$password,false);
if ($password!=='' || strcasecmp($email,$user['email'])!==0) {
    $stmt=db()->prepare('SELECT hashed_password FROM users WHERE id=?'); $stmt->bind_param('i',$user['id']); $stmt->execute();
    if (!password_verify($current,$stmt->get_result()->fetch_assoc()['hashed_password'])) $errors['current_password']='Enter your current password to change your email or password.';
}
$values=compact('name','email','phone','address');
if ($errors) form_fail('profile',$errors,'profile.php',$values);
$connection=db(); $connection->begin_transaction();
try {
    account_write_lock($connection);
    if (duplicate_account($connection,$user['username'],$email,(int)$user['id'])) {
        $connection->rollback(); form_fail('profile',['email'=>'This email address is already registered.'],'profile.php',$values);
    }
    $stmt=$connection->prepare('UPDATE users SET full_name=?,email=?,phone_number=?,addres=? WHERE id=?');
    $stmt->bind_param('ssssi',$name,$email,$phone,$address,$user['id']); $stmt->execute();
    if ($password!=='') {
        $hash=password_hash($password,PASSWORD_DEFAULT); $stmt=$connection->prepare('UPDATE users SET hashed_password=? WHERE id=?');
        $stmt->bind_param('si',$hash,$user['id']); $stmt->execute(); session_regenerate_id(true);
    }
    $connection->commit();
} catch (Throwable $error) { $connection->rollback(); throw $error; }
flash('success','Your account details have been updated.'); redirect('profile.php');
