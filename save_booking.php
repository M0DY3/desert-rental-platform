<?php
session_start();
include 'php/db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('User not logged in');
}

$user_id = $_SESSION['user_id'];
$item_id = $_POST['item_id'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$total_amount = $_POST['total_amount'] ?? null;

if (!$item_id || !$start_date || !$end_date || !$total_amount) {
    http_response_code(400);
    exit('Missing required fields');
}

// Ensure the dates are in correct format
$start_date = date('Y-m-d H:i:s', strtotime($start_date)); // Convert to correct datetime format
$end_date = date('Y-m-d H:i:s', strtotime($end_date)); // Convert to correct datetime format

// Get the current date for the booking date
$booking_date = date('Y-m-d H:i:s');

// Insert the booking details into the booking table
$stmt = $conn->prepare("INSERT INTO booking (UserID, BookingDate, StartDate, EndDate, TotalPrice, ItemID, Status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')");
$stmt->bind_param('issdii', $user_id, $booking_date, $start_date, $end_date, $total_amount, $item_id);

if ($stmt->execute()) {
    // Optionally update points for the user here
    // (We can skip this part if you handle the points separately)
    echo "Booking saved successfully!";
} else {
    http_response_code(500);
    echo "Error saving booking";
}
?>
