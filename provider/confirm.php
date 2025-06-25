<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php');  // Correct path to db_config.php

// Check if the item data is in session
if (!isset($_SESSION['item_data']) || !isset($_SESSION['item_id'])) {
    header('Location: add_item.php');  // Redirect to add_item.php if session data is not set
    exit();
}

$item_id = $_SESSION['item_id'];  // Get the item ID from session
$provider_id = $_SESSION['provider_id'];  // Get the provider ID from session

// Here we can update another field (e.g., availability, or just leave the item as "confirmed" without using a status column).
// Example: Let's assume we mark the item as 'confirmed' by updating the item availability or any other field.

// Update the item (using availability or any other field you want to update):
$query = "UPDATE item SET Price = Price WHERE ItemID = ? AND ProviderID = ?"; // No actual change, just for confirmation.

$stmt = $conn->prepare($query);

// Check if the prepare statement was successful
if ($stmt === false) {
    die('Error preparing the SQL query: ' . $conn->error);
}

// Bind the parameters for the query (item ID and provider ID)
$stmt->bind_param("ii", $item_id, $provider_id);

// Execute the query
if (!$stmt->execute()) {
    die('Error executing the query: ' . $stmt->error);
}

// If successful, you can optionally clear session data or perform any other actions
unset($_SESSION['item_data']); // Clear item data session

// Redirect the user to the services page or a confirmation page
header('Location: services.php');
exit();
?>
