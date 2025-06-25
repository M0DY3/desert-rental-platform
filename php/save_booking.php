<?php
session_start();
include 'db_config.php'; // Ensure the path is correct

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    // If user_id is not set in the session, display an error
    echo "Error: User ID not found in session.";
    exit();
}

// Get booking details from the form
$user_id = $_SESSION['user_id'];
$item_id = $_POST['item_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$delivery_address = $_POST['delivery_address'] ?? ''; // Default to empty string if not set
$total_price = (float) $_POST['total_price']; // Ensure total_price is a float

// Check if the item is available for the selected dates
$sql = "SELECT * FROM booking WHERE ItemID = ? AND (
            (StartDate <= ? AND EndDate >= ?) OR 
            (StartDate <= ? AND EndDate >= ?)
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issss', $item_id, $start_date, $start_date, $end_date, $end_date); // Fixed binding for the availability check
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Error: This item is already booked for the selected dates.";
    exit();
}

// Insert the booking details into the `booking` table
$status = 'Pending';  // Default status for new bookings
$insert_booking_sql = "INSERT INTO booking (UserID, ItemID, BookingDate, StartDate, EndDate, Status, TotalPrice) 
                       VALUES (?, ?, NOW(), ?, ?, ?, ?)";
$stmt_booking = $conn->prepare($insert_booking_sql);
$stmt_booking->bind_param('iisssd', $user_id, $item_id, $start_date, $end_date, $status, $total_price);

if ($stmt_booking->execute()) {
    // Get the booking ID of the last inserted booking
    $booking_id = $stmt_booking->insert_id;

    // Insert the order into the `orders` table
    $order_status = 'Pending';  // Default order status
    $payment_status = 'Not Paid';  // Default payment status

    // Ensure we're passing 8 values to bind_param
    // Bind the parameters to the prepared statement
    $insert_order_sql = "INSERT INTO orders (UserID, BookingID, OrderDate, TotalAmount, PaymentStatus, DeliveryAddress, OrderStatus, ItemID) 
                         VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($insert_order_sql);

    // Check if delivery address is empty, assign it properly
    if (empty($delivery_address)) {
        $delivery_address = 'N/A'; // Assign a default value if the address is empty
    }

    // Correct the number of bind parameters (8 variables)
    $stmt_order->bind_param('iissssii', $user_id, $booking_id, $total_price, $payment_status, $delivery_address, $order_status, $item_id);

    if ($stmt_order->execute()) {
        echo "Booking confirmed! Your order will be processed.";
    } else {
        echo "Error saving order: " . $stmt_order->error;
    }
} else {
    echo "Error saving booking: " . $stmt_booking->error;
}
?>
