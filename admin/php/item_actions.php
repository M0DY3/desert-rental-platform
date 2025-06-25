<?php
include 'db_config.php';

$action = $_POST['action'] ?? '';
if (!$action) die("Invalid request");

switch ($action) {
    case 'create':
        $stmt = $conn->prepare("
            INSERT INTO item (Name, Description, Price, Type, Availability, Latitude, Longitude, City, MapsURL)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssdssddss",
            $_POST['Name'], $_POST['Description'], $_POST['Price'],
            $_POST['Type'], $_POST['Availability'],
            $_POST['Latitude'], $_POST['Longitude'],
            $_POST['City'], $_POST['MapsURL']
        );
        $stmt->execute();
        break;

    case 'delete':
        $stmt = $conn->prepare("DELETE FROM item WHERE ItemID = ?");
        $stmt->bind_param("i", $_POST['ItemID']);
        $stmt->execute();
        break;
}

$conn->close();
header("Location: ../Equipment Inventory.php");
exit;
