<?php
require_once __DIR__ . '/db/app.php';
require_post();$user=require_user();
function booking_error(array $errors): never {
    if (wants_json()) json_response(['error'=>'Check your booking details.','errors'=>$errors],422);
    form_fail('booking',$errors,'booking.php',['address'=>post('address'),'pickup_date'=>post('pickup_date')]);
}
$bookingToken=post('booking_token');
if (!preg_match('/^[a-f0-9]{32}$/D',$bookingToken) || !isset($_SESSION['booking_tokens'][$bookingToken])) booking_error(['form'=>'Reload the booking page before placing your order.']);
if (is_int($_SESSION['booking_tokens'][$bookingToken])) {
    $receipt=$_SESSION['booking_tokens'][$bookingToken];
    if (wants_json()) json_response(['order_id'=>$receipt,'redirect'=>url('my_orders.php')]);
    redirect('my_orders.php');
}
rate_limit('booking',20,3600,(string)$user['id']);
$address=post('address');$pickupDate=post('pickup_date');$errors=[];
$date=DateTimeImmutable::createFromFormat('!Y-m-d',$pickupDate);
if ($address==='' || text_length($address)>255) $errors['address']='Enter a pickup address of up to 255 characters.';
if (!$date || $date->format('Y-m-d')!==$pickupDate || $date<new DateTimeImmutable('today') || $date>new DateTimeImmutable('+1 year')) $errors['pickup_date']='Choose a pickup date from today through the next year.';
$requested=$_POST['items']??[];
if (!is_array($requested) || count($requested)>100) $errors['items']='Choose valid service quantities.';
if ($errors) booking_error($errors);
$items=[];
foreach ($requested as $id=>$quantity) {
    if ($quantity==='0' || $quantity==='' || $quantity===0) continue;
    $optionId=filter_var($id,FILTER_VALIDATE_INT);$amount=filter_var($quantity,FILTER_VALIDATE_INT);
    if (!$optionId || $optionId<1 || !$amount || $amount<1 || $amount>1000) booking_error(['items'=>'Use whole-number quantities between 0 and 1,000.']);
    $items[]=['option_id'=>$optionId,'quantity'=>$amount];
}
if (!$items) booking_error(['items'=>'Select at least one service to book.']);
$connection=db();$connection->begin_transaction();
try {
    $totalCents=0;
    $lookup=$connection->prepare('SELECT option_id,service_id,price FROM service_options WHERE option_id=? FOR UPDATE');
    foreach ($items as &$item) {
        $lookup->bind_param('i',$item['option_id']);$lookup->execute();$option=$lookup->get_result()->fetch_assoc();
        if (!$option) { $connection->rollback();booking_error(['items'=>'A selected service is no longer available. Reload and select again.']); }
        $item['service_id']=(int)$option['service_id'];$item['price']=$option['price'];$totalCents+=cents($option['price'])*$item['quantity'];
    }
    unset($item);
    if ($totalCents<=0 || $totalCents>9999999999) { $connection->rollback();booking_error(['items'=>'Choose a valid order total.']); }
    $total=number_format($totalCents/100,2,'.','');
    $stmt=$connection->prepare('INSERT INTO orders (user_id,address,pickup_date,total) VALUES (?,?,?,?)');
    $stmt->bind_param('isss',$user['id'],$address,$pickupDate,$total);$stmt->execute();$orderId=$connection->insert_id;
    $insert=$connection->prepare('INSERT INTO order_items (order_id,service_id,service_option_id,quantity,price) VALUES (?,?,?,?,?)');
    foreach ($items as $item) { $insert->bind_param('iiiis',$orderId,$item['service_id'],$item['option_id'],$item['quantity'],$item['price']);$insert->execute(); }
    $connection->commit();$_SESSION['booking_tokens'][$bookingToken]=$orderId;
} catch (Throwable $error) {
    $connection->rollback();error_log('Booking failed: '.$error);
    if (wants_json()) json_response(['error'=>'Your booking could not be saved. Please try again.'],500);
    flash('error','Your booking could not be saved. Please try again.');redirect('booking.php');
}
flash('success','Booking #'.$orderId.' is scheduled. You can track it below.');
if (wants_json()) json_response(['order_id'=>$orderId,'redirect'=>url('my_orders.php')],201);
redirect('my_orders.php');
