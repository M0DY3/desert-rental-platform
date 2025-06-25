<?php
session_start(); // Start session to check if user is logged in
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get item ID and selected dates from the form
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

    // Validate dates
    if (empty($start_date) || empty($end_date)) {
        die("Please select both start and end dates.");
    }

    // Check if the end date is after the start date
    if (strtotime($end_date) <= strtotime($start_date)) {
        die("End date must be after start date.");
    }

    // Check if the item is available for the selected dates
    $sql_check = "SELECT * FROM bookings WHERE ItemID = ? AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?)
                  )";

    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('issss', $item_id, $end_date, $start_date, $start_date, $end_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        die("Selected dates are already booked. Please choose different dates.");
    }

    // If available, proceed to book the item
    $sql_book = "INSERT INTO bookings (ItemID, start_date, end_date, UserID) VALUES (?, ?, ?, ?)";
    $stmt_book = $conn->prepare($sql_book);
    
    // Assuming you have user information in session
    $user_id = $_SESSION['user_id']; // Replace with your actual session user ID retrieval
    $stmt_book->bind_param('issi', $item_id, $start_date, $end_date, $user_id);
    
    if ($stmt_book->execute()) {
        echo "Booking confirmed! Your dates are from $start_date to $end_date.";
    } else {
        die("Booking failed: " . $stmt_book->error);
    }
} else {
    die("Invalid request.");
}
?>
