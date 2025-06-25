<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('User not logged in');
}

// Get POST data from the form
$card_number = $_POST['card_number'] ?? null;

if (!$card_number) {
    exit('Card number is required');
}

// Simulate payment process: Accept any card number
$payment_successful = true; // In a real-world scenario, you would validate the card number

if ($payment_successful) {
    // Fake payment processing: update the booking status to 'Paid'
    $booking_id = $_GET['booking_id'] ?? null;
    if (!$booking_id) {
        exit('Booking ID is required');
    }

    $stmt = $conn->prepare("UPDATE booking SET Status = 'Paid' WHERE BookingID = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();

    // Redirect to the success page or display success message
    echo "🎉 Payment confirmed! Your booking is now paid. <a href='account.php'>View your bookings</a>";
} else {
    echo "Sorry, payment failed. Please try again.";
}
?>
