<?php
  require_once "db/config_session.php";
  require_once "db/signup_MVC/view.php";
  require_once "db/login_MVC/view.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="components/header/header.css">
  <link rel="stylesheet" href="components/footer/footer.css">
  <link rel="stylesheet" href="login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
  <?php
  require_once 'components/header/header.php';
  ?>
  <section class="login-signup-container">
    <div class="container">
    <h3 class="text-message">
    <?php
      output_username();
    ?>
  </h3>
    <?php
        if(!isset($_SESSION["user_id"])){?>
      <h2>Welcome to EcoLeaf</h2>
      <!-- login Form -->
      <div id="login-form" class="form-container">
        
          <h3>Login</h3>
          <form action="db/db_login.php" method="post" id="login">
            <input type="text" name="username" id="login-username" placeholder="Username">
            <input type="password" name="password" id="login-password" placeholder="Password">
            <input type="submit" value="Login">
          </form>
        <?php 
          check_login_errors();
        ?>
        <p>Don't have an account? <a href="#" id="toggle-signup">Sign Up</a></p>
        <div id="login-error" class="error-message"></div>
        <?php } ?>
      </div>

      <!-- signup Form -->
      <div id="signup-form" class="form-container">
        <h3>Sign Up</h3>
        <form action="db/db_signup.php" method="post" id="signup">
          <?php
          signup_inputs();
          ?>
          <input type="submit" value="Submit">
        </form>
        <?php
          check_signup_errors();
        ?>
        <div id="signup-error" class="error-message"></div>
        <p>Already have an account? <a href="#" id="toggle-login">Login</a></p>        
      </div>
    </div>
  </section>
  <?php
  require "components/footer/footer.php";
  ?>
  <script src="login.js"></script>
</body>
</html>