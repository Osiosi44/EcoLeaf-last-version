<?php

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_admin'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['phone'];
    $address = $_POST['address'];
    $role = 'admin';

    try {
        require "../db/db_con.php";
        require_once "../db/signup_MVC/model.php";
        require_once "../db/signup_MVC/contr.php";

        $errors =[];

        if(is_input_empty($name, $username, $contact, $address, $email, $password)){
            $errors["empty_input"] = "Please fill all fields!";
        }
        if(is_email_invalid($email)){
            $errors["invalid_email"] = "Invalid email used!";
        }
        if(is_username_taken($con, $username)){
            $errors["username_taken"] = "Username already taken!";
        }
        if(is_email_registered($con, $email)){
            $errors["email_used"] = "Email already registered!";
        }
        if(is_contact_registered($con, $contact)){
            $errors["contact_used"] = "Contact number already registered!";
        }

        require_once "config_session.php";

        if($errors){
            $_SESSION["signup_data"] = $signupData;

            header("Location: ../login.php");
            die();
        }

        $query = "INSERT INTO users (full_name, username, phone_number, addres, email, hashed_password, role) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $stmt = $con->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT,['cost'=>12]);
        
        $stmt->bind_param("sssssss", $name, $username, $contact, $address, $email, $hashed_password, $role);
        $stmt->execute();
        
        header("Location: ../account.php?signup=success");
        $stmt->close();
        
        die();

    } catch (Exception $e) {
        die("Query failed: " . $e->getMessage());
    }
}


$adminQuery = "SELECT * FROM users WHERE role = 'admin' AND id = ?";
$stmt = $con->prepare($adminQuery);

if (!$stmt) {
    die("Prepare failed: " . htmlspecialchars($con->error));
}


$stmt->bind_param("s", $_SESSION['user_id']); 

if (!$stmt->execute()) {
    die("Execute failed: " . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result(); 

if ($result && ($admin = $result->fetch_assoc())) { ?>
    <h2>Admin Profile</h2>
    <p>Name: <?php echo htmlspecialchars($admin['full_name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($admin['email']); ?></p>
<?php } else { ?>
    <p>Admin profile not found.</p>
<?php }
$stmt->close();
?>
<button class="select-btn" onclick="showSignup()">Add Admin</button>
    
<?php require_once "../db/signup_MVC/view.php"?>
<section id="s-form">
    <form method="post" class="signup-form">
        <input type="text" name="name" id="signup-name" placeholder="Full Name">
        <input type="email" name="email" id="signup-email" placeholder="Email">
        <input type="text" name="username" id="signup-username" placeholder="Username">
        <input type="password" name="password" id="signup-password" placeholder="Password">
        <input type="text" name="phone" id="signup-phone" placeholder="Phone">
        <input type="text" name="address" id="signup-address" placeholder="Address">
        <input type="submit" name="create_admin" value="Submit">
        <button type="button" class="cancel-btn" onclick="hideSignup()">Cancel</button>
    </form>
    <?php
        check_signup_errors();
    ?>
</section>
    <form action="../db/db_logout.php" id="logout">
        <button type = "submit" class="logout-btn" id="logoutbtn">Logout</button>
    </form>