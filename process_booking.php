<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'php/db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('User not logged in');
}

$user_id = $_SESSION['user_id'];
$item_id = $_POST['item_id'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$coupon_code = $_POST['coupon_code'] ?? null; // Get the coupon code from the form

if (!$item_id || !$start_date || !$end_date) {
    http_response_code(400);
    exit('Missing required fields');
}

// Fetch item details
$stmt = $conn->prepare("SELECT i.Price, i.Name, i.Description, i.City, img.ImageURL
                        FROM item i
                        LEFT JOIN img img ON i.ItemID = img.ItemID
                        WHERE i.ItemID = ?");
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$item = $result->fetch_assoc()) {
    http_response_code(404);
    exit('Item not found');
}

$price = (float) $item['Price'];
$item_name = $item['Name'];
$item_description = $item['Description'];
$item_city = $item['City'];

// Ensure $item_images is an array (even if there's only one image)
$item_images = explode(",", $item['ImageURL']); // This will handle one or multiple images
$order_date = date('Y-m-d H:i:s');

// Calculate number of days
$start_date_obj = new DateTime($start_date);
$end_date_obj = new DateTime($end_date);
$interval = $start_date_obj->diff($end_date_obj);
$number_of_days = $interval->days;

// Calculate the total price
$total_price = $price * $number_of_days;

// Apply coupon code
$discount = 0;
if ($coupon_code) {
    $stmt_coupon = $conn->prepare("SELECT * FROM promotions WHERE Code = ? AND IsActive = 1 AND CURDATE() BETWEEN StartDate AND EndDate");
    $stmt_coupon->bind_param('s', $coupon_code);
    $stmt_coupon->execute();
    $coupon_result = $stmt_coupon->get_result();
    if ($coupon = $coupon_result->fetch_assoc()) {
        $discount = $coupon['DiscountPercentage'];
        $total_price -= ($total_price * ($discount / 100)); // Apply discount
    }
}

// Save booking to the database
$stmt_booking = $conn->prepare("INSERT INTO booking (UserID, ItemID, StartDate, EndDate, TotalPrice, BookingDate)
                                VALUES (?, ?, ?, ?, ?, ?)");
$stmt_booking->bind_param('iissds', $user_id, $item_id, $start_date, $end_date, $total_price, $order_date);
$stmt_booking->execute();

// If booking is saved successfully
if ($stmt_booking->affected_rows > 0) {
    // Update loyalty points (1 point per $2 spent)
    $loyalty_points = floor($total_price / 2);
    $stmt_points = $conn->prepare("UPDATE loyaltyprogram SET Points = Points + ? WHERE UserID = ?");
    $stmt_points->bind_param('ii', $loyalty_points, $user_id);
    $stmt_points->execute();
    
    echo json_encode(['status' => 'success', 'message' => 'Booking confirmed!', 'loyalty_points' => $loyalty_points]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Booking failed.']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details</title>
    <link rel="stylesheet" href="css/RentalServices.css">
    <style>
        body { background-color: #f4f4f9; color: #333; font-family: 'Arial', sans-serif; }
        .container { width: 80%; margin: 0 auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .booking-details { display: flex; flex-direction: column; align-items: center; margin-top: 20px; }
        .booking-details h2 { font-size: 2em; color: #28a745; }
        .booking-details p { font-size: 1.2em; }
        .item-images { display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .item-images img { width: 150px; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer; }
        .item-images img.active { border: 3px solid #28a745; }
        .payment-method { margin-top: 20px; }
        .payment-method select { padding: 8px; border-radius: 5px; margin: 10px; }
        .payment-fields { display: none; margin-top: 20px; }
        .payment-fields input { padding: 8px; margin: 10px 0; border-radius: 5px; width: 100%; }
        .pay-now-btn { background-color: #28a745; color: white; padding: 12px 30px; border-radius: 5px; cursor: pointer; font-size: 1.2em; border: none; }
        .pay-now-btn:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <div class="booking-details">
        <h2>Booking Details</h2>
        <p><strong>Item:</strong> <?= htmlspecialchars($item_name) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($item_description) ?></p>
        <p><strong>Price:</strong> $<?= htmlspecialchars($price) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($start_date) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($end_date) ?></p>

        <div class="item-images">
            <?php foreach ($item_images as $index => $image): ?>
                <img src="<?= htmlspecialchars($image) ?>" alt="Item Image" onclick="changeImage(<?= $index ?>)" class="item-image <?= $index === 0 ? 'active' : '' ?>" id="item-image-<?= $index ?>">
            <?php endforeach; ?>
        </div>

        <div class="payment-method">
            <label for="payment-method">Select Payment Method:</label>
            <select id="payment-method" onchange="togglePaymentFields()">
                <option value="">--Select--</option>
                <option value="credit-card">Credit Card</option>
                <option value="paypal">PayPal</option>
                <option value="bank-transfer">Bank Transfer</option>
            </select>
        </div>

        <div class="payment-fields" id="credit-card-fields">
            <h3>Credit Card Information</h3>
            <input type="text" id="card-number" placeholder="Card Number" maxlength="16" />
            <input type="text" id="expiry-date" placeholder="Expiry Date (MM/YY)" maxlength="5" />
            <input type="text" id="security-code" placeholder="Security Code (CVV)" maxlength="3" />
        </div>

        <div class="payment-fields" id="paypal-fields">
            <h3>PayPal Information</h3>
            <input type="email" id="paypal-email" placeholder="Enter PayPal Email" />
        </div>

        <div class="payment-fields" id="bank-transfer-fields">
            <h3>Bank Transfer Information</h3>
            <input type="text" id="bank-account" placeholder="Enter Bank Account Number" />
        </div>

        <button class="pay-now-btn" onclick="processPayment()">Pay Now</button>
    </div>
</div>

<script>
function changeImage(index) {
    const images = document.querySelectorAll('.item-images img');
    images.forEach((img, i) => {
        img.classList.toggle('active', i === index);
    });
}

function togglePaymentFields() {
    document.getElementById('credit-card-fields').style.display = 'none';
    document.getElementById('paypal-fields').style.display = 'none';
    document.getElementById('bank-transfer-fields').style.display = 'none';

    const selected = document.getElementById('payment-method').value;
    if (selected) {
        document.getElementById(selected + '-fields').style.display = 'block';
    }
}

function processPayment() {
    const method = document.getElementById('payment-method').value;
    if (method === '') {
        alert('Please select a payment method.');
        return;
    }

    // Calculate loyalty points (1 point for every $2)
    const totalAmount = <?= $price ?>;
    const loyaltyPoints = Math.floor(totalAmount / 2); // 1 point per $2 spent

    // Send the booking details to the server to save the booking and update the points
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_booking.php', true);  // Calls the PHP page to save the booking
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('🎉 Your payment was successful and booking is saved!');
            // Show the user the points they earned
            alert(`You earned ${loyaltyPoints} loyalty points for this purchase!`);
            window.location.href = 'account.php';  // Redirect to the user account page
        } else {
            alert('❌ Error saving booking.');
            console.error(xhr.responseText);
        }
    };
    xhr.send('user_id=<?= $_SESSION['user_id'] ?>&item_id=<?= $item_id ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&total_amount=' + totalAmount);
}

</script>

</body>
</html>
