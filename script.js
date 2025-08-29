let menu = document.querySelector("#menu-btn");
let navbar = document.querySelector(".header .navbar");
menu.onclick = ()=>{
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active');
}
window.onscroll = ()=>{
    menu.classList.remove('fa-times');
    navbar.classList.remove('active');
}
document.addEventListener('DOMContentLoaded', function () {
    
    var homeSwiper = new Swiper(".home-slider", {
        autoplay: {
            delay: 3000,
            disableOnInteraction: false
        },
        navigation: {
            nextEl: '.swiper-button-next', 
            prevEl: '.swiper-button-prev', 
          },
        loop: true
    });

    
    var reviewsSwiper = new Swiper(".reviews-slider", {
        loop: true,
        spaceBetween: 20,
        autoHeight: true,
        grabCursor: true,
        autoplay: {
            delay: 2000, 
            disableOnInteraction: false 
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });
});


// display user information
function displayProfile() { 
    const Profile = document.querySelector(".profile_wrapper");

    document.body.appendChild(Profile);
    Profile.style.display = "flex";

    const editButton = document.querySelector(".edit-button");
    const cancelButton = document.querySelector(".cancel-button");
    const profileInfo = document.querySelector(".profile-info");
    const updateForm = document.querySelector(".update-form");
    const closeButton = document.querySelector(".close-button");

    editButton.addEventListener("click", function () {
        profileInfo.style.display = "none";
        updateForm.style.display = "block";
    });

    cancelButton.addEventListener("click", function () {
        profileInfo.style.display = "block";
        updateForm.style.display = "none";
    });


    closeButton.addEventListener("click", function () {
        document.querySelector(".profile_wrapper").style.display = "none";
    });    
}

function toggleDetails(orderId) {
    const detailsRow = document.querySelector(`.order-details-row-${orderId}`);
    if (detailsRow.style.display === 'table-row') {
        detailsRow.style.display = 'none';
    } else {
        detailsRow.style.display = 'table-row';
    }
}


function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `order_id=${orderId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); 
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while canceling the order.');
        });
    }
}



