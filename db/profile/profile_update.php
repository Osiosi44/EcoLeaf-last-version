<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        require_once "../db_con.php"; 

        require_once "../config_session.php";
        $username = $_SESSION["user_username"];

        $query = "UPDATE users SET full_name=?, phone_number=?, addres=?, email=?, hashed_password=? WHERE username = ?;";
        $stmt = $con->prepare($query);
        
        $stmt->bind_param("ssssss", $name, $contact, $address, $email, $hashed_password, $username);
        $stmt->execute();


        header("Location: ../../home.php?update=success");
        $stmt->close();
        $con->close();
        die();

    } catch (Exception $ex) {
        $errorMessage = $ex->getMessage();

        if (strpos($errorMessage, "Duplicate entry") !== false) {
            if (strpos($errorMessage, "username") !== false) {
                $errorMessage = "Username already exists. Please choose a different username.";
            }
            elseif (strpos($errorMessage, "email") !== false) {
                $errorMessage = "Email already exists. Please use a different email address.";
            }
            elseif (strpos($errorMessage, "phone_number") !== false) {
                $errorMessage = "Phone number already exists. Please use a different phone number.";
            }
            else {
                $errorMessage = "A duplicate entry was found. Please check your inputs.";
            }
        }

        echo "<script>alert('Error: " . $errorMessage . "');</script>";
        header("Refresh: 0; url=../../login.php");
        die();
    }
} else {
    header("Location: ../../login.php");
    die();
}










//-------------------------------Model---------------------------------
// function get_username(object $con, string $username){
//     $query = "SELECT username FROM users WHERE username = ?;";
//     $stmt = $con->prepare($query);
//     $stmt->bind_Param('s', $username);
//     $stmt->execute();

//     $result = $stmt->get_result();
//     return $result->fetch_assoc();
// }

// function get_email(object $con, string $email){
//     $query = "SELECT email FROM users WHERE email = ?;";
//     $stmt = $con->prepare($query);
//     $stmt->bind_Param('s', $email);
//     $stmt->execute();

//     $result = $stmt->get_result();
//     return $result->fetch_assoc();
// }

// function get_contact(object $con, string $contact){
//     $query = "SELECT phone_number FROM users WHERE phone_number = ?;";
//     $stmt = $con->prepare($query);
//     $stmt->bind_Param('s', $contact);
//     $stmt->execute();

//     $result = $stmt->get_result();
//     return $result->fetch_assoc();
// }

//------------------------------Control----------------------------------


// function is_username_taken(object $con, string $username){
//     if(get_username($con, $username)){
//         return true;
//     }else{
//         return false;
//     }
// }

// function is_email_registered(object $con, string $email){
//     if(get_email($con, $email)){
//         return true;
//     }else{
//         return false;
//     }
// }

// function is_contact_registered(object $con, string $contact){
//     if(get_contact($con, $contact)){
//         return true;
//     }else{
//         return false;
//     }
// }

//---------------------------view---------------------------------------------------------------

// function check_update_errors() {
//     if(isset($_SESSION['errors_update'])){
//         $errors = $_SESSION['errors_update'];

//         foreach($errors as $error){
//             echo '<p class = "error-messages">' . htmlspecialchars($error) . '</p>';
//         }
//         unset($_SESSION['errors_updates']);

//     }else if(isset($_GET["update"]) && $_GET["update"] === "success"){
//         echo "<script>
//         alert('Your Profile was Updated successful!');
//         </script>";
//     }
// }