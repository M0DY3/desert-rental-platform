<?php
session_start();
require 'php/db_config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];

$item_id = $_POST['item_id'] ?? null;
$total_amount = $_POST['total_amount'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$order_date = date('Y-m-d H:i:s');

if (!$item_id || !$total_amount || !$start_date || !$end_date) {
    http_response_code(400);
    exit('Missing fields');
}

$stmt = $conn->prepare("
    INSERT INTO orders (UserID, ItemID, OrderDate, StartDate, EndDate, TotalAmount, PaymentStatus, OrderStatus)
    VALUES (?, ?, ?, ?, ?, ?, 'Paid', 'Completed')
");
$stmt->bind_param('iisssd', $user_id, $item_id, $order_date, $start_date, $end_date, $total_amount);

if ($stmt->execute()) {
    http_response_code(200);
    echo "Order saved successfully.";
} else {
    http_response_code(500);
    echo "Failed to save order.";
}

$stmt->close();
$conn->close();
?>
