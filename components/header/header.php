<section class="header">
    <a href="home.php" class="logo" aria-label="EcoLeaf home">
        <img src="images/leaf.png" alt="" width="36" height="36">
        <span>Eco<span class="leaf">Leaf</span></span>
    </a>
    <nav class="navbar">
        <a href="home.php">home</a>
        <a href="about.php">about us</a>
        <a href="package.php">services</a>
        <?php
            if(isset($_SESSION["user_id"])){?>    
            <a href="my_orders.php">Orders</a>
            <button class="profile-icon" onclick="displayProfile()"><b><?php echo $_SESSION["user_username"]?></b></button>
        <?php } else{?>
            <a href="login.php" id="login-signup">Login/signup</a>
        <?php }
        ?>
        
    </nav>
    <div id="menu-btn" class="fas fa-bars">

    </div>
</section>

<?php
if (isset($_SESSION['user_id'])) {
    require "db/profile/profile_fetch.php";
    ?>
<section class="profile_wrapper">
    <div class="container">
        <div class="profile-info">
            <button class="close-button">&times;</button>
            <h1>Profile</h1>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['addres']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <div class="button-container">
                <button class="edit-button">Edit Profile</button>
                <form action="db/db_logout.php" id="logout">
                    <button type = "submit" class="logout-btn">Logout</button>
                </form>
                <form action="db/db_deleteUser.php" method="post">
                    <!-- hidden username input -->
                    <input type="hidden" name="username" value="<?php echo $_SESSION['user_username'];?>">
                    <button type = "submit" class="delete-btn">Delete Account</button>
                </form>
            </div>
        </div>
        

        
        <form action="db/profile/profile_update.php" method="POST" class="update-form">
            <h1>Update Profile</h1>
            <label for="full_name">Full Name:</label>
            <input type="text" name="name" class="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone" class="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
            
            <label for="address">Address:</label>
            <input type="text" name="address" class="address" value="<?php echo htmlspecialchars($user['addres']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" class="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Update Profile</button>
            <button type="button" class="cancel-button">Cancel</button>
        </form>
    </div>
</section>
<?php
}

if (isset($_GET['update']) && $_GET['update'] == 'success') {
    echo "<script>
        alert('Your Profile was Updated successfully!');
    </script>";
}
?>