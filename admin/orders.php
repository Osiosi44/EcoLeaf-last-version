<?php
require "../db/db_con.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $con->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("ss", $new_status, $order_id);
    $stmt->execute();

    header("Location: adminDash.php");
    exit;
}

$ordersQuery = "
        SELECT 
        o.order_id,
        u.full_name,
        o.total,
        o.status,
        so.option_name,
        oi.quantity,
        oi.price
    FROM 
        orders o
    JOIN 
        users u ON o.user_id = u.id
    LEFT JOIN 
        order_items oi ON o.order_id = oi.order_id
    LEFT JOIN 
        service_options so ON oi.service_option_id = so.option_id
    ORDER BY 
        o.order_id DESC;
";

$result = $con->query($ordersQuery);

$rows = [];

while ($row = $result->fetch_assoc()) {
    $orderId = $row['order_id'];

    if (!isset($rows[$orderId])) {
        $rows[$orderId] = [
            'order_id' => $row['order_id'],
            'full_name' => $row['full_name'], 
            'total' => $row['total'],             
            'status' => $row['status'],           
            'items' => []                         
        ];
    }

    if ($row['option_name'] !== null) { 
        $rows[$orderId]['items'][] = [
            'option_name' => $row['option_name'], 
            'quantity' => $row['quantity'],       
            'price' => $row['price']              
        ];
    }
}


if ($result->num_rows > 0): ?>
    <h2>Orders</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['total']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']);?></td>
                    
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                            <select name="status">
                                <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $row['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="complete" <?php echo $row['status'] == 'complete' ? 'selected' : ''; ?>>Completed</option>

                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                    <td>
                        <button onclick="showOrderDetails(<?php echo $row['order_id']; ?>)">Details</button>
                    </td>
                </tr>

                <tr class="order-details-row-<?php echo $row['order_id']; ?>" style="display: none;">
                    <td colspan="6">
                        <div class="order-details">
                            <ul>
                                <?php if (!empty($row['items'])): ?>
                                    <?php foreach ($row['items'] as $item): ?>
                                        <li>
                                            <?php echo htmlspecialchars($item['option_name']); ?> - 
                                            Quantity: <?php echo htmlspecialchars($item['quantity']); ?>
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
<?php else: ?>
    <p>No orders found.</p>
<?php endif;

?>