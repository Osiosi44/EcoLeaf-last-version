<?php
require_once __DIR__ . '/app.php';
require_post();
rate_limit('signup', 8, 3600);
$name=post('name'); $username=post('username'); $email=strtolower(post('email')); $phone=post('phone'); $address=post('address');
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$next=safe_next(post('next'));
$destination='login.php?mode=signup&next=' . rawurlencode($next);
$values=compact('name','username','email','phone','address');
$errors=account_errors($name,$username,$email,$phone,$address,$password);
if ($errors) form_fail('signup',$errors,$destination,$values);
$connection=db(); $connection->begin_transaction();
try {
    account_write_lock($connection);
    if ($duplicate=duplicate_account($connection,$username,$email)) {
        $connection->rollback(); form_fail('signup',[$duplicate=>'This '.$duplicate.' is already registered.'],$destination,$values);
    }
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $stmt=$connection->prepare('INSERT INTO users (full_name,username,email,phone_number,addres,hashed_password) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('ssssss',$name,$username,$email,$phone,$address,$hash); $stmt->execute(); $connection->commit();
} catch (Throwable $error) { $connection->rollback(); throw $error; }
flash('success','Your account is ready. Sign in to book your first pickup.');
redirect('login.php?next='.rawurlencode($next));
