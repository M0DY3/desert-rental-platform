<?php
include 'db_config.php';

// Get BookingID from the query string (URL)
$BookingID = $_GET['BookingID'] ?? null;

// Check if BookingID is provided, if not show an error message
if (!$BookingID) {
    die("Invalid request - BookingID is missing.");
}

// Prepare and execute the query to fetch the booking details
$stmt = $conn->prepare("SELECT * FROM booking WHERE BookingID = ?");
$stmt->bind_param("i", $BookingID);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// If no booking is found, show an error
if (!$booking) {
    die("Booking not found.");
}
?>

<!-- Form to Edit Booking -->
<form method="POST" action="booking_actions.php">
    <input type="hidden" name="action" value="update"> <!-- Action type to specify this is an update -->
    <input type="hidden" name="BookingID" value="<?= htmlspecialchars($booking['BookingID']) ?>"> <!-- Hidden BookingID for identification -->
    
    <label for="UserID">UserID:</label>
    <input name="UserID" value="<?= htmlspecialchars($booking['UserID']) ?>" required>
    
    <label for="StartDate">Start Date:</label>
    <input name="StartDate" value="<?= htmlspecialchars($booking['StartDate']) ?>" required>
    
    <label for="EndDate">End Date:</label>
    <input name="EndDate" value="<?= htmlspecialchars($booking['EndDate']) ?>" required>
    
    <label for="TotalPrice">Total Price:</label>
    <input name="TotalPrice" value="<?= htmlspecialchars($booking['TotalPrice']) ?>" required>
    
    <button type="submit">Update Booking</button>
</form>

<?php
$conn->close(); // Close the database connection
?>

