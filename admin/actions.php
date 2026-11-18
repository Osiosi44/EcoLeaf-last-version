<?php
require_once __DIR__.'/../db/app.php';
require_post();$admin=require_admin();rate_limit('admin-write',120,3600,(string)$admin['id']);
$action=post('action');$connection=db();$tab='overview';
if($action==='update_order') {
    $tab='orders';$id=filter_var($_POST['order_id']??null,FILTER_VALIDATE_INT);$status=post('status');
    if(!$id || !in_array($status,['in_progress','complete','cancelled'],true)) reject(422,'Choose a valid order status.');
    $connection->begin_transaction();
    try {
        $stmt=$connection->prepare('SELECT status FROM orders WHERE order_id=? FOR UPDATE');$stmt->bind_param('i',$id);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();
        $transitions=['pending'=>['in_progress','cancelled'],'in_progress'=>['complete','cancelled'],'complete'=>[],'cancelled'=>[]];
        if(!$row || !in_array($status,$transitions[$row['status']]??[],true)) { $connection->rollback();flash('error','This status change is no longer available. Reload the order and try again.');redirect('admin/adminDash.php?tab=orders'); }
        $stmt=$connection->prepare('UPDATE orders SET status=? WHERE order_id=?');$stmt->bind_param('si',$status,$id);$stmt->execute();$connection->commit();
    } catch(Throwable $error) { $connection->rollback();throw $error; }
    flash('success','Order #'.$id.' has been updated.');
} elseif($action==='save_service' || $action==='add_service') {
    $tab='services';$name=post('service_name');$description=post('description');
    try { $priceCents=cents(post('price')); } catch(InvalidArgumentException $error) { flash('error',$error->getMessage());redirect('admin/adminDash.php?tab=services'); }
    if(text_length($name)<3 || text_length($name)>50 || text_length($description)>2000 || $priceCents<1) { flash('error','Use a service name of 3-50 characters, a description up to 2,000 characters, and a positive price.');redirect('admin/adminDash.php?tab=services'); }
    $price=number_format($priceCents/100,2,'.','');
    $optionPrices=[];
    if($action==='save_service') {
        $id=filter_var($_POST['service_id']??null,FILTER_VALIDATE_INT);
        if(!$id || !is_array($_POST['prices']??[])) reject(422,'Choose a valid service.');
        foreach(($_POST['prices']??[]) as $optionId=>$optionPrice) {
            try { $optionCents=is_string($optionPrice)?cents($optionPrice):0; } catch(InvalidArgumentException $error) { $optionCents=0; }
            if(!ctype_digit((string)$optionId) || $optionCents<1) { flash('error','Each option needs a positive price with up to two decimal places.');redirect('admin/adminDash.php?tab=services'); }
            $optionPrices[(int)$optionId]=number_format($optionCents/100,2,'.','');
        }
    } else {
        $optionName=post('option_name');
        if(text_length($optionName)<2 || text_length($optionName)>50) { flash('error','Enter a first service option of 2-50 characters.');redirect('admin/adminDash.php?tab=services'); }
    }
    $connection->begin_transaction();
    try {
        if($action==='save_service') {
            $stmt=$connection->prepare('SELECT service_id FROM services WHERE service_id=? FOR UPDATE');$stmt->bind_param('i',$id);$stmt->execute();
            if(!$stmt->get_result()->fetch_assoc()) { $connection->rollback();reject(404,'That service no longer exists.'); }
            $stmt=$connection->prepare('UPDATE services SET service_name=?,description=?,price=? WHERE service_id=?');$stmt->bind_param('sssi',$name,$description,$price,$id);$stmt->execute();
            foreach($optionPrices as $optionId=>$optionPrice) {
                $stmt=$connection->prepare('UPDATE service_options SET price=? WHERE option_id=? AND service_id=?');$stmt->bind_param('sii',$optionPrice,$optionId,$id);$stmt->execute();
            }
        } else {
            $picture1='images/leaf.png';$picture2='images/laundry.jpg';
            $stmt=$connection->prepare('INSERT INTO services (service_name,description,price,picture1,picture2) VALUES (?,?,?,?,?)');$stmt->bind_param('sssss',$name,$description,$price,$picture1,$picture2);$stmt->execute();$id=$connection->insert_id;
            $stmt=$connection->prepare('INSERT INTO service_options (service_id,option_name,price) VALUES (?,?,?)');$stmt->bind_param('iss',$id,$optionName,$price);$stmt->execute();
        }
        $connection->commit();
    } catch(Throwable $error) { $connection->rollback();throw $error; }
    flash('success',$action==='add_service'?'Your new service is available for booking.':'Service details and prices have been saved. Existing order prices are unchanged.');
} elseif($action==='create_admin') {
    $tab='account';rate_limit('create-admin',10,3600,(string)$admin['id']);
    $current=is_string($_POST['current_password']??null)?$_POST['current_password']:'';
    $stmt=$connection->prepare('SELECT hashed_password FROM users WHERE id=?');$stmt->bind_param('i',$admin['id']);$stmt->execute();
    if(!password_verify($current,$stmt->get_result()->fetch_assoc()['hashed_password'])) { flash('error','Enter your current password to create another administrator.');redirect('admin/adminDash.php?tab=account'); }
    $name=post('name');$username=post('username');$email=strtolower(post('email'));$phone=post('phone');$address='';$password=is_string($_POST['password']??null)?$_POST['password']:'';
    $errors=account_errors($name,$username,$email,$phone,$address,$password);
    if($errors) { flash('error',implode(' ',$errors));redirect('admin/adminDash.php?tab=account'); }
    $connection->begin_transaction();
    try {
        account_write_lock($connection);
        if($duplicate=duplicate_account($connection,$username,$email)) { $connection->rollback();flash('error','This '.$duplicate.' is already registered.');redirect('admin/adminDash.php?tab=account'); }
        $hash=password_hash($password,PASSWORD_DEFAULT);$role='admin';
        $stmt=$connection->prepare('INSERT INTO users (full_name,username,email,phone_number,addres,hashed_password,role) VALUES (?,?,?,?,?,?,?)');$stmt->bind_param('sssssss',$name,$username,$email,$phone,$address,$hash,$role);$stmt->execute();$connection->commit();
    } catch(Throwable $error) { $connection->rollback();throw $error; }
    flash('success','The administrator account has been created.');
} else reject(422,'Choose a supported action.');
redirect('admin/adminDash.php?tab='.$tab);
