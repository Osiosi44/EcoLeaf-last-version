<?php
require "db/db_con.php";

$query = "SELECT service_name, picture2, price, description FROM services";
$result = $con->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="box">
            <div class="image">
                <img src="' . htmlspecialchars($row['picture2']) . '" alt="">
            </div>
            <div class="content">
                <h3>' . htmlspecialchars($row['service_name']) . '</h3>
                <h3>Starting with $' . htmlspecialchars($row['price']) . '</h3>
                <ul>
                    <li>'.htmlspecialchars($row['description']).'</li>
                    <li><br><br></li>
                </ul>';
                if(isset($_SESSION["user_id"])){
                    echo'<button class="btn bookingbtn">book</button>';
                }else{ 
                    echo '<a href="login.php" class="btn">book</a>';
                };
                echo '
            </div>
        </div>';
    }
} else {
    echo "No services found.";
}

$con->close();
?>