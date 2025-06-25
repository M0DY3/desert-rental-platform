<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

// Get the data from Step 1
if (!isset($_POST['item_id'], $_POST['start_date'], $_POST['end_date'])) {
    header('Location: booking_step1.php');
    exit();
}

$item_id = $_POST['item_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Fetch the item details from the database
$sql = "SELECT * FROM item WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item_result = $stmt->get_result();
$item = $item_result->fetch_assoc();

if (!$item) {
    echo "Item not found.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking</title>
    <link rel="stylesheet" href="css/RentalServices.css">
</head>
<body>

<header>
    <h1>Confirm Booking for <?= htmlspecialchars($item['Name']) ?></h1>
</header>

<main>
    <div class="container">
        <h3>Item: <?= htmlspecialchars($item['Name']) ?></h3>
        <p>Description: <?= htmlspecialchars($item['Description']) ?></p>
        <p>Price per day: $<?= htmlspecialchars($item['Price']) ?></p>
        <p>Location: <?= htmlspecialchars($item['City']) ?></p>

        <h4>Booking Dates:</h4>
        <p>Start Date: <?= htmlspecialchars($start_date) ?></p>
        <p>End Date: <?= htmlspecialchars($end_date) ?></p>

        <form method="POST" action="booking_step3.php">
            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['ItemID']) ?>">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</main>

</body>
</html>
