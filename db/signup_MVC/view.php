<?php

declare(strict_types=1);

function signup_inputs(){
//return name
    if(isset($_SESSION["signup_data"]["name"])){
        echo '<input type="text" name="name" id="signup-name" placeholder="Full Name" value="' . $_SESSION["signup_data"]["name"] . '" >';
    }else{
        echo '<input type="text" name="name" id="signup-name" placeholder="Full Name">';
    }
//return email
    if(isset($_SESSION["signup_data"]["email"]) && !isset($_SESSION["errors_signup"]["email_used"]) && !isset($_SESSION["errors_signup"]["invalid_email"])) {
        echo '<input type="email" name="email" id="signup-email" placeholder="Email" value="' . $_SESSION["signup_data"]["email"] . '" >';
    }else{
        echo '<input type="email" name="email" id="signup-email" placeholder="Email">';
    }
//return username
    if(isset($_SESSION["signup_data"]["username"]) && !isset($_SESSION["errors_signup"]["username_taken"])) {
        echo '<input type="text" name="username" id="signup-username" placeholder="Username" value="' . $_SESSION["signup_data"]["username"] . '" >';
    }else{
        echo '<input type="text" name="username" id="signup-username" placeholder="Username">';
    }
//return password
    echo '<input type="password" name="password" id="signup-password" placeholder="Password">';
//return phone
    if(isset($_SESSION["signup_data"]["contact"]) && !isset($_SESSION["errors_signup"]["contact_used"])) {
        echo '<input type="text" name="phone" id="signup-phone" placeholder="Phone" value="' . $_SESSION["signup_data"]["contact"] . '" >';
    }else{
        echo '<input type="text" name="phone" id="signup-phone" placeholder="Phone">';
    }
//return address
    if(isset($_SESSION["signup_data"]["address"])) {
        echo '<input type="text" name="address" id="signup-address" placeholder="Address" value="'. $_SESSION["signup_data"]["address"] . '" >';
    }else{
        echo '<input type="text" name="address" id="signup-address" placeholder="Address">';
    }
}




function check_signup_errors() {
    if(isset($_SESSION['errors_signup'])){
        $errors = $_SESSION['errors_signup'];

        echo "<script>
            function toggleSignupForm() {
            document.querySelector('title').textContent = 'Sign Up';
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('signup-form').style.display = 'block';
          }

          toggleSignupForm();
        </script>";

        foreach($errors as $error){
            echo '<script> 
            alert("'.htmlspecialchars($error).'"); 
            </script> 
            <p class = "error-messages">' . htmlspecialchars($error) . '</p>';
        }
        unset($_SESSION['errors_signup']);

    }else if(isset($_GET["signup"]) && $_GET["signup"] === "success"){
        echo "<script>
        alert('Sign up successful. Please log in.');
        </script>";
    }
}