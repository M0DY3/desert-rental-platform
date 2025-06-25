<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get tomorrow's date
$tomorrow = new DateTime();
$tomorrow->modify('+1 day');  // Move the date to tomorrow
$tomorrow_date = $tomorrow->format('Y-m-d');

// Fetch confirmed bookings starting tomorrow or later, with valid EndDate
$sql = "
    SELECT 
        o.OrderID, o.OrderDate, o.StartDate, o.EndDate, o.TotalAmount, o.PaymentStatus, o.OrderStatus, 
        i.ItemID, i.Name, i.Description, i.Price
    FROM orders o
    JOIN item i ON o.ItemID = i.ItemID
    WHERE o.UserID = ? 
    AND o.OrderStatus = 'Confirmed'  -- Only confirmed orders
    AND o.StartDate >= ?  -- Only future bookings (starting tomorrow or later)
    AND o.EndDate != '0000-00-00 00:00:00'  -- Ensure EndDate is valid (not empty)
    ORDER BY o.StartDate ASC  -- Sort by StartDate
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $user_id, $tomorrow_date);  // Bind the user ID and tomorrow's date
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Upcoming</title>
    <link rel="stylesheet" href="css/RentalServices.css">
    <style>
        /* CSS for styling the page */
        body { background-color: #f4f4f9; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .basket-container { margin: 30px auto; max-width: 1000px; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .basket-item { background-color: #2e2e2e; margin-bottom: 20px; padding: 20px; border-radius: 8px; display: flex; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: 0.3s; }
        .basket-item:hover { transform: scale(1.02); }
        .basket-item img { width: 150px; height: 100px; object-fit: cover; border-radius: 8px; margin-right: 20px; }
        .basket-details { flex-grow: 1; }
        .basket-details h3 { margin: 0; margin-bottom: 10px; font-size: 24px; color: #4CAF50; }
        .basket-details p { margin: 5px 0; font-size: 16px; color: #ddd; line-height: 1.5; }
        .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
        .details-grid p { background: rgba(255,255,255,0.1); padding: 8px; border-radius: 5px; font-size: 14px; }
        .remove-button { margin-top: 15px; background-color: #e60000; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .remove-button:hover { background-color: #cc0000; }
        .remove-button[disabled] { background-color: #666; cursor: not-allowed; }
        .no-bookings { font-size: 22px; color: #fff; text-align: center; margin-top: 100px; }
    </style>
</head>
<body>

<header>
    <img src="images/logo.png" alt="logo">
    <h1 style="text-align: center;">My Upcoming Orders</h1>
</header>

<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="rental_services.php">Services</a></li>
        <li><a href="account.php">Account</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<main>
    <div class="basket-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="basket-item">
                    <?php
                    // Fetch item image for each booking
                    $sql_image = "SELECT ImageURL FROM img WHERE ItemID = ?";
                    $stmt_image = $conn->prepare($sql_image);
                    $stmt_image->bind_param('i', $row['ItemID']);
                    $stmt_image->execute();
                    $result_image = $stmt_image->get_result();
                    $image = $result_image->fetch_assoc();
                    ?>
                    <?php if ($image): ?>
                        <img src="<?= htmlspecialchars($image['ImageURL']) ?>" alt="<?= htmlspecialchars($row['Name']) ?>">
                    <?php else: ?>
                        <img src="images/default.jpg" alt="Default Image">
                    <?php endif; ?>

                    <div class="basket-details">
                        <h3><?= htmlspecialchars($row['Name']) ?></h3>
                        <p><?= htmlspecialchars($row['Description']) ?></p>

                        <div class="details-grid">
                            <p><strong>Price:</strong> $<?= htmlspecialchars($row['Price']) ?></p>
                            <p><strong>Total:</strong> $<?= htmlspecialchars($row['TotalAmount']) ?></p>
                            <p><strong>Order Date:</strong> <?= htmlspecialchars($row['OrderDate']) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($row['OrderStatus']) ?></p>
                            <p><strong>Start Date:</strong> <?= htmlspecialchars($row['StartDate']) ?></p>
                            <p><strong>End Date:</strong> <?= htmlspecialchars($row['EndDate']) ?: 'Not Set' ?></p>
                        </div>

                        <!-- You can add functionality for canceling or other actions here -->
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-bookings">You have no upcoming orders.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p style="text-align: center;">&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>
