<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if booking_id is passed
if (!isset($_GET['booking_id'])) {
    header('Location: index.php');
    exit();
}

$booking_id = $_GET['booking_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Complete</title>
    <link rel="stylesheet" href="css/RentalServices.css">
</head>
<body>

<header>
    <h1>Booking Completed</h1>
</header>

<main>
    <div class="container">
        <h2>Thank you for your booking!</h2>
        <p>Your booking has been successfully completed. Booking ID: <?= htmlspecialchars($booking_id) ?></p>
        <p>You will receive a confirmation email shortly with all the details of your booking.</p>
        <a href="index.php">Go to Home</a>
    </div>
</main>

</body>
</html>
