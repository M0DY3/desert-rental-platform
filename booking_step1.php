<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

// Get the item_id from the URL query string
if (!isset($_GET['item_id'])) {
    header('Location: print_items.php');
    exit();
}

$item_id = $_GET['item_id'];

// Fetch the item details
$sql = "SELECT * FROM item WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

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
    <title>Select Booking Dates</title>
    <link rel="stylesheet" href="css/RentalServices.css">
</head>
<body>

<header>
    <h1>Select Dates for <?= htmlspecialchars($item['Name']) ?></h1>
</header>

<main>
    <div class="container">
        <form method="POST" action="booking_step2.php">
            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['ItemID']) ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required><br>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required><br><br>

            <button type="submit">Next</button>
        </form>
    </div>
</main>

</body>
</html>
