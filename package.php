<?php
    require_once "db/config_session.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About</title> 
    <link rel="stylesheet" href="services.css">
    <link rel="stylesheet" href="styles.css">
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
    <?php
        require_once 'components/header/header.php';
    ?>

    <div class="heading" style="background-image: linear-gradient(rgba(0, 0, 0, 0.889), rgba(0, 0, 0,0.889)), url('images/machine_bg.png'); no-repeat;background-position:center">
        <h1>Our Packages</h1>
    </div>



    <section class="packages">
        <h1 class="heading-title">
            Packages
        </h1>
        <div class="box-container">
    <?php
    require_once 'db/services/services_fetch.php';
    ?>
        </div>
    </section>


<?php
require 'booking.php';
?>




<?php
    require_once 'components/footer/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="script.js"></script> 
<script src="services.js"></script>
</body>
</html>
