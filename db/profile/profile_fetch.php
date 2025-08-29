<?php
require_once "db/config_session.php"; 
require_once "db/db_con.php";

$username = $_SESSION['user_username'];

$query = "SELECT full_name, username, phone_number, addres, email FROM users WHERE username = ?;";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "User does not exist";
    header("Location: login.php"); 
    die();
}

$user = $result->fetch_assoc();

$stmt->close();
$con->close();
?>