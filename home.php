<?php
    require_once "db/config_session.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
    <?php
    require_once 'components/header/header.php';
    ?>

    <section class="home">
        <div class="swiper home-slider">
            <div class="swiper-wrapper">
            <div class="swiper-slide slide" style="background: url(images/male.png) no-repeat ">
                    <div class="content">
                       
                        <h3>The laundry of your choice!</h3>
                        <?php if(isset($_SESSION["user_id"])){?>
                        <a href="package.php" class="btn">Start Booking Now</a><?php }else{?>
                        <a href="login.php" class="btn">Get Started</a>
                        <?php } ?>
                    </div>
            </div>
                <div class="swiper-slide slide" style="background-image: linear-gradient(rgba(0, 0, 0, 0.889), rgba(0, 0, 0,0.889)), url('images/machine_bg.png'); no-repeat;background-position:center">
                    <div class="content-2">; 
                        <span>wash fold dry and get it delivered</span>
                        <h3>The laundry of your choice!</h3>
                        <?php if(isset($_SESSION["user_id"])){?>
                        <a href="package.php" class="btn">Start Booking Now</a><?php }else{?>
                        <a href="login.php" class="btn">Get Started</a>
                        <?php } ?>
                    </div>
                </div>
                <div class="swiper-slide slide" style="background: url(images/hanged_clothes.png) no-repeat ">
                    <div class="content">
                       
                        <h3>The laundry of your choice!</h3>
                        <?php if(isset($_SESSION["user_id"])){?>
                        <a href="package.php" class="btn">Start Booking Now</a><?php }else{?>
                        <a href="login.php" class="btn">Get Started</a>
                        <?php } ?>
                    </div>
            </div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>



    <section class="services">
        <h1 class="heading-title">
            Our Services
        </h1>
        <div class="box-container">
<?php
require_once 'db/services/home_services.php';
?>
        </div>
    </section>

    <section class="home-about">
        <div class="image">
            <img src="images/laundry-guy.png" alt="">
        </div>
        <div class="content">
            <h3>about us</h3>
            <p>We are here to make laundry day a breeze, because you deserve a little more time for yourself.Let us handle the mess while you enjoy the freshness!</p>
            <a href="about.php" class="btn">Read More</a>
        </div>
    </section>


    <section class="home-packages">
    
        <div class="content">
            <h3>Packages</h3>
            <p>At EcoLeaf, we offer a wide range of laundry solutions designed to make your life easier. Whether you need everyday laundry care or specialized treatments, we have got you covered. </p>
            <a href="package.php" class="btn">See More</a>
        </div>
        <div class="image">
            <img src="images/packages-img.png" alt="">
        </div>
    </section>

    <section class="packages">
        <h1 class="heading-title">
            Packages
        </h1>
        <div class="box-container">
            
<?php
require_once 'db/services/home2_services.php';
?>

        </div>
    </section>
    <section class="reviews">
        <h2>REVIEWS</h2>
    <div class="swiper reviews-slider">
        <div class="swiper-wrapper">
            <?php
            require_once 'db/fetch_reviews.php';
            ?>
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
