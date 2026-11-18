<?php
require_once __DIR__ . '/app.php';
require_post();$user=require_user();$id=filter_var($_POST['order_id']??null,FILTER_VALIDATE_INT);
if (!$id || $id<1) reject(422,'Choose a valid order.');
$stmt=db()->prepare("UPDATE orders SET status='cancelled' WHERE order_id=? AND user_id=? AND status='pending'");
$stmt->bind_param('ii',$id,$user['id']);$stmt->execute();
flash($stmt->affected_rows?'success':'error',$stmt->affected_rows?'Your booking has been cancelled.':'This order cannot be cancelled. Only scheduled orders can be cancelled.');
redirect('my_orders.php');
