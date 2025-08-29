<?php
require "db/db_con.php";

$query = "SELECT reviews.*,users.username 
          FROM reviews 
          JOIN users ON reviews.user_id = users.id 
          ORDER BY reviews.review_id DESC 
          LIMIT 5";
$result = $con->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']); 
        $comment = nl2br(htmlspecialchars($row['comment'])); 
        $rating = intval($row['rating']); 

        echo '<div class="swiper-slide slide comment_slide">';
        echo '    <div class="stars">';

        for ($i = 1; $i <= $rating; $i++) {
            if ($i <= $rating) {
                echo '<i class="fas fa-star" style="--fa-primary-color: #ffcc00; --fa-secondary-color: #ffcc00;"></i>';
            } else {
                echo '<i class="fas fa-star" style="--fa-primary-color: #ccc; --fa-secondary-color: #ccc;"></i>';
            }
        }

        echo '    </div>';
        echo '    <p>' . $comment . '</p>'; 
        echo '    <h3>' . $username . '</h3>'; 
        echo '    <span>Client</span>';
        echo '</div>';
    }
} else {
    echo '<div class="swiper-slide slide">';
    echo '    <p>No reviews available.</p>';
    echo '</div>';
}

$con->close();
?>