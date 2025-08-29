<?php
require "db/db_con.php";

$query = "SELECT s.service_id, s.service_name, s.description, s.price AS base_price, 
          o.option_id, o.option_name, o.price AS option_price
          FROM services s 
          LEFT JOIN service_options o ON s.service_id = o.service_id";

$result = $con->query($query);

$services = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (stripos($row['service_name'], 'delivery') !== false) {
            continue;
        }
        $serviceId = $row['service_id'];
        $serviceName = $row['service_name'];
        $description = $row['description'];
        $basePrice = $row['base_price'];

        if (!isset($services[$serviceId])) {
            $services[$serviceId] = [
                'service_id' => $serviceId,
                'service_name' => $serviceName,
                'description' => $description,
                'base_price' => $basePrice,
                'options' => []
            ];
        }

        if (!empty($row['option_name'])) {
            $services[$serviceId]['options'][] = [
                'option_id' => $row['option_id'],
                'name' => $row['option_name'],
                'price' => $row['option_price']
            ];
        }
    }
} else {
    echo "No services found.";
}

$con->close();
?>
<section class="services_section">
    <div class="services_wrapper">
        <h1>Pick your choices ...</h1>
        <button class="hide-services" onclick="hidebooking()">&times;</button>

        <form id="bookingForm" action="process_order.php" method="POST">
            <!-- part 1 -->
            <div class="services_s">
                <h2>Select Services</h2>
                <div class="services_container_1">
                    <div class="services">
                        <?php foreach ($services as $serviceId => $service): ?>
                            <button type="button" class="service_1 service-<?php echo htmlspecialchars($service['service_id']) ?>" onclick="toggleServiceList('service-<?php echo htmlspecialchars($service['service_id']) ?>-list', 'service-<?php echo htmlspecialchars($service['service_id']) ?>')">
                                <h2><?php echo htmlspecialchars($service['service_name']) ?></h2>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="services_list">
                        <?php foreach ($services as $serviceId => $service): ?>
                            <div id="service-<?php echo htmlspecialchars($service['service_id']) ?>-list" class="ser_list hidden">
                                <h3><?php echo htmlspecialchars($service['service_name']) ?> Options</h3>
                                <?php foreach ($service['options'] as $option): ?>
                                    <label>
                                        <input type="checkbox" class="check_name" value="<?php echo htmlspecialchars($option['name']) ?>" data-option-id="<?php echo htmlspecialchars($option['option_id']) ?>" data-price="<?php echo htmlspecialchars($option['price']) ?>">
                                        <?php echo htmlspecialchars($option['name']) ?> - $<?php echo htmlspecialchars($option['price']) ?>
                                        <input type="number" class="qty-input" min="0" placeholder="Qty" name="quantity_<?php echo htmlspecialchars($option['option_id']) ?>" data-option-id="<?php echo htmlspecialchars($option['option_id']) ?>">
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="selection_container">
                    <h2>Your services</h2>
                    <ul id="selectedItems">

                    </ul>
                    <button class="nextBtn hidden" type="button">Next</button>
                </div>
            </div>

            <!-- part 2 -->
            <div class="finish_booking hidden">
                <h2>Finish Booking</h2>
                <div class="flex">
                    <div class="inputBox">
                        <span>Address:</span>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="inputBox">
                        <span>Pickup Date</span>
                        <input type="date" id="pickup_date" name="pickup_date" required>
                    </div>
                    <!-- <div class="inputBox">
                        <label for="delivery_date">Delivery Date</label>
                        <input type="date" id="delivery_date" name="delivery_date" readonly>
                    </div> -->
                    <div class="inputBox">
                        <span>Total Price</span>
                        <input type="text" id="t_price" name="total_price" readonly>
                    </div>
                </div>
                <button type="submit">Submit Booking</button>
            </div>
        </form>
    </div>
</section>