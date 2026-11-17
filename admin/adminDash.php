<?php
require_once __DIR__ . '/../db/config_session.php';
require "../db/db_con.php";
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">

        <div class="sidebar">
            <ul>
                <li class="sidebar-username">
                    <span>Welcome, <strong><?php echo $_SESSION['user_username'];?></strong></span>
                </li>
                <br>
                <li><button onclick="showContent('orders')">Orders</button></li>
                <li><button onclick="showContent('users')">Users</button></li>
                <li><button onclick="showContent('services')">Services</button></li>
                <li><button onclick="showContent('account')">Account</button></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="content" id="content">
            <div id="orders" class="tab-content">
                <?php require 'orders.php'; ?>
            </div>
            <div id="users" class="tab-content hidden">
                <?php require 'users.php'; ?>
            </div>
            <div id="services" class="tab-content hidden">
                <?php include 'services.php'; ?>
            </div>
            <div id="account" class="tab-content hidden">
                <?php include 'account.php'; ?>
            </div>
        </div>
    </div>
    <script defer src="script.js"></script>
</body>
</html>