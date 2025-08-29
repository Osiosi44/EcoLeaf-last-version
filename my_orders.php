<?php
require 'db/db_con.php';
require_once "db/config_session.php";

$user_id = $_SESSION['user_id'];

// Fetch orders and their associated items in one query
$sql = "
    SELECT 
        o.order_id, 
        o.address, 
        o.pickup_date, 
        o.status, 
        o.total,
        oi.item_id AS item_id,
        so.option_name AS option_name,
        oi.quantity AS quantity,
        oi.price AS price
    FROM 
        orders o
    LEFT JOIN 
        order_items oi ON o.order_id = oi.order_id
    LEFT JOIN 
        service_options so ON oi.service_option_id = so.option_id
    WHERE 
        o.user_id = ? AND o.status != 'cancelled'
    ORDER BY 
        o.order_id DESC;
";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_id' => $row['order_id'],
            'address' => $row['address'],
            'pickup_date' => $row['pickup_date'],
            'status' => $row['status'],
            'total' => $row['total'],
            'items' => []
        ];
    }
    if ($row['item_id']) {
        $orders[$orderId]['items'][] = [
            'option_name' => $row['option_name'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="styles.css"> 
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
    <?php
    require_once 'components/header/header.php';
    ?>

    <div class="heading" style="background:url('images/machine_bg.png') no-repeat">
    </div>
    <section class="booking">
        <h1 class="heading-title">Orders</h1>
    </section>

    <section>
        <h1>Your Orders</h1>
        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Address</th>
                        <th>Pickup Date</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['address']); ?></td>
                            <td><?php echo htmlspecialchars($order['pickup_date']); ?></td>
                            <td>$<?php echo htmlspecialchars($order['total']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td>
                                <div class="order-buttons">
                                    <!-- Cancel Button -->
                                    <button class="cancel-btn" onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                        Cancel
                                    </button>
                                    <!-- Details Button -->
                                    <button class="details-btn" onclick="toggleDetails(<?php echo $order['order_id']; ?>)">
                                        View Details
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Order Details Dropdown -->
                        <tr class="order-details-row-<?php echo $order['order_id']; ?>" style="display: none;">
                            <td colspan="6">
                                <div class="order-details">
                                    <ul>
                                        <?php if (!empty($order['items'])): ?>
                                            <?php foreach ($order['items'] as $item): ?>
                                                <li>
                                                    <?php echo htmlspecialchars($item['option_name']); ?> - 
                                                    Quantity: <?php echo htmlspecialchars($item['quantity']); ?> - 
                                                    $<?php echo htmlspecialchars($item['price']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No items found for this order.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <!-- leave a comment -->
         <div class="leave-comment">
            <h2>Leave a Comment</h2>
            <form action="db/db_review.php" method="post">
                <textarea name="comment" id="comment" cols="30" rows="10" placeholder="Write your comment here..."></textarea><br><br>
                
                <!-- Rating Input -->
                <label for="rating">Rating (1-5):</label>
                <select name="rating" id="rating" required>
                    <option value="" disabled selected>Select a rating</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select><br><br>

                <button type="submit">Submit</button>
            </form>
        </div>
    </section>


<?php
    require_once 'components/footer/footer.php';
?>


<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="script.js"></script> 
</body>
</html>
