<?php
session_start();
include 'db_config.php'; // Ensure this path is correct

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get booking details from the form
$item_id = $_POST['item_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$delivery_address = $_POST['delivery_address'] ?? ''; // Default empty if not set
$total_price = (float) $_POST['total_price']; // Ensure total_price is a float

// Query to get the item details
$sql = "SELECT * FROM item WHERE ItemID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item_result = $stmt->get_result();

if ($item_result->num_rows == 0) {
    echo "Item not found.";
    exit();
}

$item = $item_result->fetch_assoc();

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    // Check if the item is available for the selected dates
    $sql = "SELECT * FROM booking WHERE ItemID = ? AND (
                (StartDate <= ? AND EndDate >= ?) OR 
                (StartDate <= ? AND EndDate >= ?)
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $item_id, $start_date, $start_date, $end_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: This item is already booked for the selected dates.";
        exit();
    }

    // Insert booking details into the `booking` table
    $status = 'Pending';  // Default status for new bookings
    $insert_booking_sql = "INSERT INTO booking (UserID, ItemID, BookingDate, StartDate, EndDate, Status, TotalPrice) 
                           VALUES (?, ?, NOW(), ?, ?, ?, ?)";
    $stmt_booking = $conn->prepare($insert_booking_sql);
    $stmt_booking->bind_param('iisssd', $user_id, $item_id, $start_date, $end_date, $status, $total_price);

    if ($stmt_booking->execute()) {
        // Get the booking ID of the last inserted booking
        $booking_id = $stmt_booking->insert_id;

        // Insert the order into the `orders` table
        $order_status = 'Pending';  // Default order status
        $payment_status = 'Not Paid';  // Default payment status

        $insert_order_sql = "INSERT INTO orders (UserID, BookingID, OrderDate, TotalAmount, PaymentStatus, DeliveryAddress, OrderStatus, ItemID) 
                             VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
        $stmt_order = $conn->prepare($insert_order_sql);
        $stmt_order->bind_param('iissssii', $user_id, $booking_id, $total_price, $payment_status, $delivery_address, $order_status, $item_id);

        if ($stmt_order->execute()) {
            echo "Booking confirmed! Your order will be processed.";
        } else {
            echo "Error saving order: " . $stmt_order->error;
        }
    } else {
        echo "Error saving booking: " . $stmt_booking->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking - <?= htmlspecialchars($item['Name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        .booking-details {
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        .booking-details h2 {
            font-size: 2em;
            color: #333;
        }

        .booking-details p {
            font-size: 1.2em;
            color: #555;
        }

        .confirm-btn {
            background-color: #28a745;
            color: white;
            padding: 15px;
            font-size: 1.2em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .confirm-btn:hover {
            background-color: #218838;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

    <header class="header">
        <h1>Confirm Your Booking</h1>
    </header>

    <div class="container">
        <div class="booking-details">
            <h2>Item: <?= htmlspecialchars($item['Name']) ?></h2>
            <p><strong>Description:</strong> <?= htmlspecialchars($item['Description']) ?></p>
            <p><strong>Price per Day:</strong> $<?= htmlspecialchars($item['Price']) ?></p>
            <p><strong>Booking Dates:</strong> <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?></p>
            <p><strong>Total Price:</strong> $<?= number_format($total_price, 2) ?></p>
            <p><strong>Delivery Address:</strong> <?= htmlspecialchars($delivery_address) ?></p>
        </div>

        <form method="POST" action="" class="confirm-form">
            <input type="hidden" name="item_id" value="<?= $item['ItemID'] ?>">
            <input type="hidden" name="start_date" value="<?= $start_date ?>">
            <input type="hidden" name="end_date" value="<?= $end_date ?>">
            <input type="hidden" name="delivery_address" value="<?= $delivery_address ?>">
            <input type="hidden" name="total_price" value="<?= $total_price ?>">

            <button type="submit" class="confirm-btn">Confirm Booking</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Camping Adventures. All Rights Reserved.</p>
    </footer>

</body>
</html>
