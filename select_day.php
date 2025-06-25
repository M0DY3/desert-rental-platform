<?php 
session_start();
include 'php/db_config.php';

$message = ""; // Variable to store messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    // Get today's date
    $today = date('Y-m-d');

    // Validation checks
    if (empty($start_date) || empty($end_date)) {
        $message = "Both start date and end date are required.";
    } elseif ($start_date < $today) {
        $message = "Start date cannot be in the past.";
    } elseif ($end_date < $start_date) {
        $message = "End date cannot be before the start date.";
    } elseif (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        $message = "You need to be logged in to make a booking.";
    } else {
        $user_id = $_SESSION['user_id'];
        $item_id = $_SESSION['basket']['item_id'];

        // Check if the item is already booked within the selected dates
        $sql_check_overlap = "
            SELECT * FROM booking 
            WHERE ItemID = ? AND (
                (StartDate <= ? AND EndDate >= ?) OR
                (StartDate <= ? AND EndDate >= ?)
            )
        ";
        $stmt_check_overlap = $conn->prepare($sql_check_overlap);
        $stmt_check_overlap->bind_param('issss', $item_id, $start_date, $start_date, $end_date, $end_date);
        $stmt_check_overlap->execute();
        $result = $stmt_check_overlap->get_result();

        if ($result->num_rows > 0) {
            $message = "The item is already booked for the selected dates. Please choose another date range.";
        } else {
            // Save to session
            $_SESSION['start_date'] = $start_date;
            $_SESSION['end_date'] = $end_date;
            $_SESSION['item_id'] = $item_id;

            // Redirect to confirm booking page
            header("Location: confirm_booking.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Dates</title>
</head>
<body>
    <?php if (!empty($message)) : ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="container">
        <h1>Select Your Booking Dates</h1>

        <form method="POST" action="">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required>
            
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" required>
            
            <button type="submit">Confirm Booking</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
    </footer>
</body>
</html>
