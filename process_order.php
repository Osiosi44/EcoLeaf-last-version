<?php
require_once "db/config_session.php";
require_once 'db/db_con.php';

function createOrder($userId, $address, $pickupDate, $total, $con) {
    $orderDate = date('Y-m-d H:i:s');
    $status = 'pending';

    $sql = "INSERT INTO orders (user_id, address, pickup_date, order_date, status, total) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("issssd", $userId, $address, $pickupDate, $orderDate, $status, $total);
    $stmt->execute();

    return $stmt->insert_id; 
}

function createOrderItem($orderId, $serviceId, $optionId, $quantity, $price, $con) {
    $sql = "INSERT INTO order_items (order_id, service_id, service_option_id, quantity, price) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("iiidi", $orderId, $serviceId, $optionId, $quantity, $price);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert order item: " . $stmt->error);
    }
}

function getServiceIdFromOptionId($optionId, $con) {
    $sql = "SELECT service_id FROM service_options WHERE option_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $optionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        return $row['service_id'];
    } else {
        return null; 
    }
}

function getOptionIdFromName($optionName, $con) {
    $sql = "SELECT option_id FROM service_options WHERE option_name = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $optionName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        return $row['option_id'];
    } else {
        return null; 
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $userId = $_SESSION["user_id"];
        $address = $_POST['address'];
        $pickupDate = $_POST['pickup_date'];
        $total = floatval(str_replace('$', '', $_POST['total_price'])); // Remove dollar sign and convert to float


        if (empty($address) || empty($pickupDate) || $total <= 0) {
            throw new Exception("Invalid input: Please fill in all required fields.");
        }


        $orderId = createOrder($userId, $address, $pickupDate, $total, $con);


        foreach ($_POST as $key => $value) {
            if (strpos($key, 'option_name_') === 0) { 
                $index = substr($key, strlen('option_name_')); 
                $optionName = htmlspecialchars($value); 
                $quantity = intval($_POST["quantity_$index"]); 
                $price = floatval($_POST["price_$index"]); 
                if ($quantity > 0 && $price > 0) {
                    try {
                        $optionId = getOptionIdFromName($optionName, $con);

                        if ($optionId !== null) {
                            $serviceId = getServiceIdFromOptionId($optionId, $con);

                            if ($serviceId !== null) {
                                createOrderItem($orderId, $serviceId, $optionId, $quantity, $price, $con);
                            } else {
                                error_log("Service ID not found for option ID: $optionId");
                            }
                        } else {
                            error_log("Option ID not found for option name: $optionName");
                        }
                    } catch (Exception $e) {
                        error_log("Error processing option name $optionName: " . $e->getMessage());
                    }
                }
            }
        }
        echo "Booking successful! Your order ID is $orderId.";
    } catch (Exception $e) {
        echo "An error occurred while processing your booking: " . $e->getMessage();
    }
}

// print_r($_POST);



