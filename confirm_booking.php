<?php
session_start(); // Start session to access user details and basket
include 'php/db_config.php'; // Include database configuration

// Retrieve the session variables
$start_date = $_SESSION['start_date'] ?? '';
$end_date = $_SESSION['end_date'] ?? '';
$basket = $_SESSION['basket'] ?? [];
$user_id = $_SESSION['user_id'] ?? null; // Assuming the user is logged in and their UserID is stored in session

if (empty($start_date) || empty($end_date) || empty($basket) || !$user_id) {
    header('Location: print_items.php');
    exit();
}

// Calculate the total amount for the order
$total_amount = 0;
foreach ($basket as $item) {
    $total_amount += $item['item']['Price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert order into 'orders' table
    $order_date = date('Y-m-d H:i:s');
    $payment_status = 'Pending'; // Default status
    $order_status = 'Pending'; // Default order status
    $delivery_address = "N/A"; // Or collect this from the user as an additional form field

    // Insert order into the database
    $stmt = $conn->prepare("INSERT INTO `orders` (UserID, OrderDate, TotalAmount, PaymentStatus, DeliveryAddress, OrderStatus) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $user_id, $order_date, $total_amount, $payment_status, $delivery_address, $order_status);

    if ($stmt->execute()) {
        // Get the last inserted OrderID
        $order_id = $stmt->insert_id;

        // Insert each item into the order_items table
        foreach ($basket as $item) {
            $item_id = $item['item']['ItemID'];

            $item_stmt = $conn->prepare("INSERT INTO `order_items` (OrderID, ItemID) VALUES (?, ?)");
            $item_stmt->bind_param("ii", $order_id, $item_id);
            $item_stmt->execute();
        }

        // Clear the session basket after the order is processed
        $_SESSION['basket'] = [];

        // Redirect to a confirmation page or payment page
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();
    } else {
        echo "Error processing the order: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Booking</title>
</head>
<body>
    <header>
        <h1>Confirm Your Booking</h1>
    </header>

    <main>
        <h2>Booking Summary</h2>

        <p><strong>Start Date:</strong> <?= htmlspecialchars($start_date) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($end_date) ?></p>

        <h3>Items:</h3>
        <ul>
            <?php foreach ($basket as $item): ?>
                <li><?= htmlspecialchars($item['item']['Name']) ?> (ID: <?= htmlspecialchars($item['item']['ItemID']) ?>) - $<?= htmlspecialchars($item['item']['Price']) ?></li>
            <?php endforeach; ?>
        </ul>

        <p><strong>Total Amount:</strong> $<?= htmlspecialchars($total_amount) ?></p>

        <form action="" method="POST">
            <button type="submit">Confirm Booking</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
    </footer>
</body>
</html>
