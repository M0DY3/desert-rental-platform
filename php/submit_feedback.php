<?php
session_start();
include 'db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Capture form data
$user_id = $_POST['user_id'];
$order_id = $_POST['order_id'];
$item_id = $_POST['item_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

// Ensure all fields are not empty
if (empty($user_id) || empty($order_id) || empty($item_id) || empty($rating) || empty($comment)) {
    die("Error: Missing required fields.");
}

// Insert feedback into the database
$sql = "INSERT INTO feedback (UserID, OrderID, ItemID, Rating, Comment, CreatedAt) 
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $user_id, $order_id, $item_id, $rating, $comment);

if ($stmt->execute()) {
    echo "Feedback submitted successfully!";
    header("Location: ../orders.php"); // Redirect back to orders page
} else {
    echo "Error: Unable to submit feedback.";
}

$stmt->close();
$conn->close();
?>
