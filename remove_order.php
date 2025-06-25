<?php
session_start();
include 'php/db_config.php';

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_SESSION['user_id']);

    if (isset($_POST['order_id'])) {
        $orderId = intval($_POST['order_id']);

        $stmt = $conn->prepare("SELECT OrderDate FROM orders WHERE OrderID = ? AND UserID = ?");
        $stmt->bind_param('ii', $orderId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($order = $result->fetch_assoc()) {
            $orderDate = new DateTime($order['OrderDate']);
            $now = new DateTime();
            $hoursDiff = ($now->getTimestamp() - $orderDate->getTimestamp()) / 3600;

            if ($hoursDiff <= 24) {
                $deleteStmt = $conn->prepare("DELETE FROM orders WHERE OrderID = ? AND UserID = ?");
                $deleteStmt->bind_param('ii', $orderId, $userId);
                $deleteStmt->execute();
                $deleteStmt->close();
                $_SESSION['message'] = "Order cancelled successfully.";
            } else {
                $_SESSION['error'] = "Cannot cancel order after 24 hours.";
            }
        } else {
            $_SESSION['error'] = "Order not found.";
        }
        $stmt->close();
    }
}

header("Location: basket.php");
exit();
?>
