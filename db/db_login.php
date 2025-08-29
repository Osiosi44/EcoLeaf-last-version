<?php

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST["username"];
    $password = $_POST["password"];

    try {
        require_once "db_con.php";
        require_once "login_MVC/model.php";
        require_once "login_MVC/contr.php";
        
        //Error Handlers
        $errors = [];

        if(is_input_empty($username, $password)){
            $errors["empty_input"] = "Please fill all fields!";
        }

        $result = get_user($con, $username);

        if(does_user_exist($result)){
            $errors["incorrect_username"] = "Incorrect info!";
        }
        if(!does_user_exist($result) && is_password_wrong($password, $result["hashed_password"])){
            $errors["incorrect_password"] = "Wrong password!";
        }

        require_once 'config_session.php';

        if($errors){
            $_SESSION["errors_login"] = $errors;
            header("Location: ../login.php");
            die();
        }

        $newSessionId = session_create_id();
        $sessionId = $newSessionId. '_'. $result["id"];
        session_id($sessionId);

        $_SESSION["user_id"] = $result['id'];
        $_SESSION["user_username"] = htmlspecialchars($result['username']);
        $_SESSION["user_role"] = $result['role']; 
        $_SESSION['last_regeneration'] = time();

        // Redirect based on the user's role
        if ($result['role'] === 'admin') {
            header("Location: ../admin/adminDash.php");
        } else {
            header("Location: ../home.php?login=success");
        }
        
        die();

    } catch (Exception $e) {
        die("Query failed: " . $e->getMessage());
    }
}else{
    header("Location: ../login.php");
    die();
}