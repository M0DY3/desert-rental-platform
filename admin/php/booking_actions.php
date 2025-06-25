<?php
include 'db_config.php';

// Check if the action is update
$action = $_POST['action'] ?? null;
if (!$action || $action !== 'update') {
    die("Invalid request");
}

// Get the updated data from the form
$BookingID = $_POST['BookingID'] ?? null;
$UserID = $_POST['UserID'] ?? null;
$StartDate = $_POST['StartDate'] ?? null;
$EndDate = $_POST['EndDate'] ?? null;
$TotalPrice = $_POST['TotalPrice'] ?? null;

// Ensure required fields are provided
if (!$BookingID || !$UserID || !$StartDate || !$EndDate || !$TotalPrice) {
    die("Missing required fields.");
}

// Prepare the update SQL query
$stmt = $conn->prepare("UPDATE booking SET UserID = ?, StartDate = ?, EndDate = ?, TotalPrice = ? WHERE BookingID = ?");
$stmt->bind_param("issdi", $UserID, $StartDate, $EndDate, $TotalPrice, $BookingID);

// Execute the query and check for success
if ($stmt->execute()) {
    // Redirect or display a success message
    header("Location: ../Current Rentals.php?message=Booking updated successfully");
    exit;
} else {
    echo "Error updating booking: " . $stmt->error;
}

$conn->close(); // Close the database connection
?>
