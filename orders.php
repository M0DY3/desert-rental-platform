<?php 
session_start();
include 'php/db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['user'];

// Get User ID
$sql = "SELECT UserID FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    die("Error: User not found. <a href='login.php'>Login again</a>");
}

// Fetch bookings for the logged-in user, ordered by Status
$booking_sql = "SELECT * FROM `booking` WHERE `UserID` = ? ORDER BY `Status` ASC";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->bind_param("i", $user_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings</title>
    <link rel="stylesheet" href="css/bookings.css">
    <style>
        .booking-item {
            background: #f9f9f9;
            border: 1px solid #ddd;
            margin: 10px;
            padding: 15px;
            border-radius: 5px;
        }
        .booking-item h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .back-button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>My Bookings</h1>

    <?php if ($booking_result->num_rows > 0): ?>
        <div class="bookings-list">
            <?php 
            while ($booking = $booking_result->fetch_assoc()):
            ?>
                <div class="booking-item">
                    <h2>Booking #<?= htmlspecialchars($booking['BookingID']) ?></h2>
                    <div class="booking-details">
                        <p><strong>Booking Date:</strong> <?= htmlspecialchars($booking['BookingDate']) ?></p>
                        <p><strong>Start Date:</strong> <?= htmlspecialchars($booking['StartDate']) ?></p>
                        <p><strong>End Date:</strong> <?= htmlspecialchars($booking['EndDate']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($booking['Status']) ?></p>
                        <p><strong>Total Price:</strong> $<?= htmlspecialchars($booking['TotalPrice']) ?></p>
                        <p><strong>Cancellation Policy:</strong> <?= htmlspecialchars($booking['CancellationPolicy']) ?></p>
                    </div>

                    <?php
                    // Fetch Item Details based on ItemID
                    $item_sql = "SELECT * FROM item WHERE ItemID = ?";
                    $item_stmt = $conn->prepare($item_sql);
                    $item_stmt->bind_param("i", $booking['ItemID']);
                    $item_stmt->execute();
                    $item_result = $item_stmt->get_result();
                    $item = $item_result->fetch_assoc();
                    ?>

                    <?php if ($item): ?>
                        <div class="item-details">
                            <p><strong>Item Name:</strong> <?= htmlspecialchars($item['Name']) ?></p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($item['Description']) ?></p>
                            <p><strong>Price:</strong> $<?= htmlspecialchars($item['Price']) ?></p>
                            <p><strong>Type:</strong> <?= htmlspecialchars($item['Type']) ?></p>
                        </div>

                        <?php
                        $img_sql = "SELECT ImageURL FROM img WHERE ItemID = ?";
                        $img_stmt = $conn->prepare($img_sql);
                        $img_stmt->bind_param("i", $item['ItemID']);
                        $img_stmt->execute();
                        $img_result = $img_stmt->get_result();
                        while ($img = $img_result->fetch_assoc()):
                            echo "<img src='" . htmlspecialchars($img['ImageURL']) . "' alt='Item Image' style='width:150px; margin:10px 10px 10px 0;'>";
                        endwhile;
                        $img_stmt->close();
                        ?>
                    <?php else: ?>
                        <p>Item details not found.</p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>

    <a href="account.php" class="back-button">Back to Profile</a>
</div>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>

<?php
// Close all prepared statements and the database connection
$booking_stmt->close();
$conn->close();
?>
