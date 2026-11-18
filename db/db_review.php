<?php
require_once __DIR__ . '/app.php';
require_post();$user=require_user();rate_limit('review',3,3600,(string)$user['id']);
$comment=post('comment');$rating=filter_var($_POST['rating']??null,FILTER_VALIDATE_INT);
if (!$rating || $rating<1 || $rating>5 || text_length($comment)<5 || text_length($comment)>1500) {
    flash('error','Choose a rating from 1 to 5 and write a review between 5 and 1,500 characters.');redirect('my_orders.php#review');
}
$stmt=db()->prepare("SELECT order_id FROM orders WHERE user_id=? AND status='complete' LIMIT 1");$stmt->bind_param('i',$user['id']);$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) reject(403,'You can leave a review after a completed order.');
$stmt=db()->prepare('INSERT INTO reviews (user_id,rating,comment) VALUES (?,?,?)');$stmt->bind_param('iis',$user['id'],$rating,$comment);$stmt->execute();
flash('success','Thank you. Your review has been published.');redirect('my_orders.php');
