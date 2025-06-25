<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php'); // Correct path to db_config.php

// Retrieve provider data from database
$provider_id = $_SESSION['provider_id'];
$query = "SELECT * FROM provider WHERE ProviderID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$provider = $result->fetch_assoc();

// Get total number of ads (items) created by the provider
$query_total_ads = "SELECT COUNT(*) AS total_ads FROM item WHERE ProviderID = ?";
$stmt_total_ads = $conn->prepare($query_total_ads);
$stmt_total_ads->bind_param("i", $provider_id);
$stmt_total_ads->execute();
$result_total_ads = $stmt_total_ads->get_result();
$total_ads = $result_total_ads->fetch_assoc()['total_ads'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Profile</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="form-container">
        <h2>Provider Profile</h2>
        <p><strong>Name:</strong> <?php echo $provider['Name']; ?></p>
        <p><strong>Email:</strong> <?php echo $provider['Email']; ?></p>
        <p><strong>Rating:</strong> <?php echo $provider['Rating']; ?> / 5</p>
        <p><strong>Total Ads:</strong> <?php echo $total_ads; ?></p> <!-- Dynamic count of total ads -->
        
        <!-- Button to edit ads -->
        <button onclick="window.location.href='edit_item.php'">Edit Ads</button>

        <!-- Button to go back to home (services page) -->
        <button onclick="window.location.href='services.php'">Back to Home</button>
    </div>
</body>
</html>
