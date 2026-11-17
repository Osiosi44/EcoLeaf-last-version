<?php
require_once __DIR__ . '/db/config_session.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Please log in before booking.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Please submit the booking form.');
}
require __DIR__ . '/db/db_con.php';
try {
    $address = trim((string) ($_POST['address'] ?? ''));
    $pickupDate = (string) ($_POST['pickup_date'] ?? '');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $pickupDate);
    if ($address === '' || strlen($address) > 255 || !$date || $date->format('Y-m-d') !== $pickupDate) {
        throw new InvalidArgumentException('Enter a valid address and pickup date.');
    }
    $items = [];
    $totalCents = 0;
    $lookup = $con->prepare('SELECT option_id, service_id, price FROM service_options WHERE option_name = ?');
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'option_name_') !== 0) continue;
        $index = substr($key, strlen('option_name_'));
        $quantity = filter_var($_POST['quantity_' . $index] ?? null, FILTER_VALIDATE_INT);
        if (!$quantity || $quantity < 1 || $quantity > 1000 || !is_string($value)) {
            throw new InvalidArgumentException('Enter a valid item quantity.');
        }
        $lookup->bind_param('s', $value);
        $lookup->execute();
        $option = $lookup->get_result()->fetch_assoc();
        if (!$option) throw new InvalidArgumentException('A selected service is unavailable.');
        $option['quantity'] = $quantity;
        $items[] = $option;
        $totalCents += (int) round((float) $option['price'] * 100) * $quantity;
    }
    if (!$items || $totalCents <= 0) throw new InvalidArgumentException('Select at least one service.');
    $con->begin_transaction();
    $total = $totalCents / 100;
    $userId = (int) $_SESSION['user_id'];
    $order = $con->prepare('INSERT INTO orders (user_id, address, pickup_date, total) VALUES (?, ?, ?, ?)');
    $order->bind_param('issd', $userId, $address, $pickupDate, $total);
    $order->execute();
    $orderId = $con->insert_id;
    $item = $con->prepare('INSERT INTO order_items (order_id, service_id, service_option_id, quantity, price) VALUES (?, ?, ?, ?, ?)');
    foreach ($items as $option) {
        $item->bind_param('iiiid', $orderId, $option['service_id'], $option['option_id'], $option['quantity'], $option['price']);
        $item->execute();
    }
    $con->commit();
    echo 'Booking successful! Your order ID is ' . $orderId . '.';
} catch (InvalidArgumentException $exception) {
    http_response_code(422);
    echo $exception->getMessage();
} catch (Throwable $exception) {
    $con->rollback();
    error_log('Booking failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'The booking could not be saved. Please try again.';
}
