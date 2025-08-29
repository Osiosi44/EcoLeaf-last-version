function showContent(tabName) {
    console.log("Showing content for:", tabName);


    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        
        selectedTab.classList.remove('hidden');
    } else {
        console.error("Tab not found for:", tabName);
    }
}

function showOrderDetails(orderId) {
    const detailsRow = document.querySelector(`.order-details-row-${orderId}`);
    if (detailsRow.style.display === 'table-row') {
        detailsRow.style.display = 'none';
    } else {
        detailsRow.style.display = 'table-row';
    }
}

function showSignup(){
    document.getElementById('s-form').style.display = 'block';
}
function hideSignup(){
    document.getElementById('s-form').style.display = 'none';
}