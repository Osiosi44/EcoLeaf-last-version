<?php

require "db/db_con.php";


$query = "SELECT service_name, picture2, price FROM services";
$result = $con->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // if (stripos($row['service_name'], 'delivery') == true) {
        //     continue;
        // }
        echo '<div class="box">
            <div class="image">
                <img src="' . htmlspecialchars($row['picture2']) . '" alt="">
            </div>
                <div class="content">
                    <h3>' . htmlspecialchars($row['service_name']) . '</h3>
                    <h3>Starting with $' . htmlspecialchars($row['price']) . '</h3>
                    <a href="package.php" class="btn">see more</a>
                </div>
            </div>';
    }
} else {
    echo "No services found.";
}

$con->close();
?>