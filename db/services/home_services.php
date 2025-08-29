<?php
require "db/db_con.php";

$query = "SELECT service_name, picture1 FROM services";
$result = $con->query($query);


if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        echo '<div class="box">';
        echo '<img src="' . htmlspecialchars($row['picture1']) . '" alt="">';
        echo '<h3>' . htmlspecialchars($row['service_name']) . '</h3>';
        echo '</div>';
    }
} else {
    echo "No services found.";
}


$con->close();
?>