<?php
require_once "db_con.php";
require "config_session.php";

$comment = $_POST['comment'];
$user_id = $_SESSION['user_id']; 
$rating = $_POST['rating']; 


if (empty($comment) || !$user_id || ($rating && ($rating < 1 || $rating > 5))) {
    echo "Error: Please provide a valid comment and ensure the rating is between 1 and 5.";
    exit;
}


$stmt = $con->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("sss", $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo "Review submitted successfully!";
    header("Location: ../my_orders.php");
} else {
    echo "Error: " . $stmt->error;
}


$stmt->close();
$con->close();
