<?php 
    require_once "db/config_session.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>about</title>
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
<?php 
    require_once 'components/header/header.php';
    ?>

    <div class="heading" style="background-image: linear-gradient(rgba(0, 0, 0, 0.889), rgba(0, 0, 0,0.889)), url('images/machine_bg.png'); no-repeat;">
        <h1>About us</h1>
    </div>

    <section class="about">
        <div class="image">
            <img src="images/male.png" alt="">
        </div>

        <div class="content">
            <h3>why choose us</h3>
            <p>What sets us apart is our commitment to quality, reliability, and customer satisfaction. We know how much your clothes mean to you, and we treat them as if they were our own.</p>
            <p>With flexible pickup and delivery options, we bring the laundry service to your doorstep, saving you time and effort so you can focus on what truly matters.</p>
            <div class="icon-container">
                <div class="icons">
                <i class="fa-solid fa-star"></i>
                        <span>high quality</span>
                  
                </div>

                <div class="icons">
                    <i class="fas fa-hand-holding-usd" >  </i>
                        <span>affordable price</span>
                    
                </div>

                <div class="icons">
                <i class="fa-solid fa-truck"></i>
                        <span>fast delivery</span>
                  
                </div>
            </div>
        </div>
        
    </section>

<?php
    require_once 'components/footer/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="script.js"></script> 
</body>
</html>
