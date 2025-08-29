function displaybooking(){
    const bookingDiv = document.querySelector(".services_section");
    bookingDiv.style.display = "block";
}

const bookingButtons = document.querySelectorAll(".bookingbtn");
bookingButtons.forEach(button => {
    button.addEventListener('click', displaybooking);
});
function hidebooking() {
    document.querySelector('.services_section').style.display = 'none';
}

function toggleServiceList(listId, className) {
    const ser = document.querySelectorAll('.service_1');
    ser.forEach(s => {
        s.classList.remove('active');
    });

    const active = document.querySelector(`.${className}`);
    active.classList.add('active');

    const serviceLists = document.querySelectorAll('.ser_list');
    serviceLists.forEach(serviceList => {
        serviceList.classList.add('hidden');
    });

    const list = document.getElementById(listId);
    if (list) {
        list.classList.toggle('hidden');
    } else {
        console.error(`No element found with id: ${listId}`);
    }

    if (list && !list.classList.contains('hidden')) {
        addSelectedItemsToList(listId);
    }
}

function addSelectedItemsToList(listId) {
    const checkboxes = document.querySelectorAll(`#${listId} input[type='checkbox']`);
    checkboxes.forEach(checkbox => {
        const label = checkbox.parentElement; 
        const priceText = label.textContent.match(/\$(\d+(\.\d{1,2})?)/); // Extract price from text
        const price = parseFloat(priceText ? priceText[1] : 0); // Parse price as a float
        const quantityInput = label.querySelector('input[type="number"]'); // Get the associated number input

        function updateSelectedItemsList() {
            const selectedItemsList = document.getElementById('selectedItems');
            const quantity = parseInt(quantityInput.value, 10); // Parse the quantity as an integer

            const listItem = Array.from(selectedItemsList.children).find(
                item => item.getAttribute('data-value') === checkbox.value
            );

            if (quantity > 0) {
                const subTotal = price * quantity;

                if (!listItem) {
                    const newItem = document.createElement('li');
                    newItem.textContent = `${checkbox.value} - $${subTotal.toFixed(2)}`;
                    newItem.setAttribute('data-value', checkbox.value);
                    newItem.setAttribute('data-subtotal', subTotal.toFixed(2)); // Store sub-total as an attribute
                    selectedItemsList.appendChild(newItem);

                    document.querySelector('.nextBtn').classList.remove('hidden');
                } else {
                    listItem.textContent = `${checkbox.value} - $${subTotal.toFixed(2)}`;
                    listItem.setAttribute('data-subtotal', subTotal.toFixed(2)); // Update sub-total attribute
                }
            } else {
                if (listItem) {
                    selectedItemsList.removeChild(listItem);
                }

                if (selectedItemsList.children.length === 0) {
                    document.querySelector('.nextBtn').classList.add('hidden');
                }
            }
            updateTotalPrice();
        }
        quantityInput.addEventListener('input', updateSelectedItemsList);

        updateSelectedItemsList();
    });
}

function updateTotalPrice() {
    const selectedItemsList = document.getElementById('selectedItems');
    let totalPrice = 0;

    Array.from(selectedItemsList.children).forEach(item => {
        const subTotal = parseFloat(item.getAttribute('data-subtotal')) || 0;
        totalPrice += subTotal;
    });

    document.getElementById("t_price").value = `$${totalPrice.toFixed(2)}`; // Update visible price

    if (selectedItemsList.children.length === 0) {
        document.querySelector('.nextBtn').classList.add('hidden');
    } else {
        document.querySelector('.nextBtn').classList.remove('hidden');
    }
}

// update checkbox state, label style
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    const label = checkbox.parentElement; 
    const quantityInput = label ? label.querySelector('input[type="number"]') : null; 

    function updateCheckboxState() {
        if (!quantityInput) return; 

        const quantity = parseInt(quantityInput.value, 10); 

        if (quantity > 0) {
            checkbox.checked = true; 
            label.classList.add('checked'); 
        } else {
            checkbox.checked = false; 
            label.classList.remove('checked');
        }
    }

    if (quantityInput) {
        quantityInput.addEventListener('input', updateCheckboxState);
    }

    if (quantityInput) {
        updateCheckboxState();
    }
});

// Labels click
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('click', function () {
        const parentElement = this.parentElement;

        const quantityInput = parentElement.querySelector('input[type="number"]');

        if (quantityInput) {
            quantityInput.focus();
        }
    });
});

// next button click
document.querySelector('.nextBtn').addEventListener('click', () => {
    
    const selectedItemsList = document.getElementById('selectedItems');
    if (selectedItemsList.children.length === 0) {
        alert("Please select at least one item.");
        return;
    }
    
    document.querySelector('.services_s').style.display = 'none';
    document.querySelector('.finish_booking').style.display = 'block';
});

// Form submission handler
document.getElementById('bookingForm').addEventListener('submit', function (e) {
    e.preventDefault(); 

    const address = document.getElementById('address').value;
    const pickupDate = document.getElementById('pickup_date').value;
    const totalPrice = parseFloat(document.getElementById('t_price').value.replace('$', ''));

    if (!address || !pickupDate || isNaN(totalPrice)) {
        alert("Please fill in all required fields.");
        return;
    }

    const selectedItemsUl = document.getElementById('selectedItems');
    const selectedItems = [];

    Array.from(selectedItemsUl.children).forEach(li => {
        const [itemName, subTotalStr] = li.textContent.split(' - ');
        const subTotal = parseFloat(subTotalStr.replace('$', '').trim());
        const optionId = li.getAttribute('data-value');

        selectedItems.push({
            option_id: optionId,
            name: itemName.trim(),
            subtotal: subTotal,
        });
    });

    if (selectedItems.length === 0) {
        alert("Please select at least one item.");
        return;
    }

    const formData = new FormData();
    formData.append('address', address);
    formData.append('pickup_date', pickupDate);
    formData.append('total_price', totalPrice);

    selectedItems.forEach((item, index) => {
        formData.append(`option_name_${index}`, item.option_id);
        formData.append(`quantity_${index}`, 1); 
        formData.append(`price_${index}`, item.subtotal);
    });

    fetch('process_order.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Booking successful")) {
                alert('Order placed successfully!');
            } else {
                alert('Error placing order: ' + data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while processing your order.');
        });
});