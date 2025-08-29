<?php
// session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['phone'];
    $address = $_POST['address'];

    try {
        require_once "db_con.php";
        require_once "signup_MVC/model.php";
        require_once "signup_MVC/contr.php";

//----------------------------------------------------------------
        $errors = [];

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

        require_once 'config_session.php';

        if($errors){
            $_SESSION["errors_signup"] = $errors;

            $signupData = [
                "name" => $name,
                "username" => $username,
                "email" => $email,
                "contact" => $contact,
                "address" => $address,
            ];
            $_SESSION["signup_data"] = $signupData;

            header("Location: ../login.php");
            die();
        }
//----------------------------------------------------------------

        $query = "INSERT INTO users (full_name, username, phone_number, addres, email, hashed_password) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $con->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT,['cost'=>12]);
        
        $stmt->bind_param("ssssss", $name, $username, $contact, $address, $email, $hashed_password);
        $stmt->execute();
        
        header("Location: ../login.php?signup=success");
        $stmt->close();
        $con->close();
        
        die();

    } catch (Exception $e) {
        die("Query failed: " . $e->getMessage());
    }
} else {
    header("Location: ../login.php");
    die();
}