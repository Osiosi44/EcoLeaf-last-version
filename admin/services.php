<?php

require "../db/db_con.php";
$servicesQuery = "SELECT * FROM services";
$servicesResult = $con->query($servicesQuery);

if ($servicesResult->num_rows > 0): ?>
    <h2>Services</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>

            </tr>
        </thead>
        <tbody>
            <?php while ($row = $servicesResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['service_id']; ?></td>
                    <td><?php echo $row['service_name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <button class="select-btn">Add Service</button>
<?php else: ?>
    <p>No services found.</p>
<?php endif; ?>