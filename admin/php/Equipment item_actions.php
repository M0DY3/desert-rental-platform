<?php
include 'db_config.php';

// Check if the action is update
$action = $_POST['action'] ?? '';
if (!$action || $action !== 'update') {
    die("Invalid request");
}

// Get the form data
$ItemID = $_POST['ItemID'] ?? null;
$Name = $_POST['Name'] ?? null;
$Description = $_POST['Description'] ?? null;
$Price = $_POST['Price'] ?? null;
$Type = $_POST['Type'] ?? null;
$Availability = $_POST['Availability'] ?? null;
$City = $_POST['City'] ?? null;
$MapsURL = $_POST['MapsURL'] ?? null;

// Ensure required fields are provided
if (!$ItemID || !$Name || !$Description || !$Price || !$Type || !$Availability) {
    die("Missing required fields.");
}

// Prepare the update query
$stmt = $conn->prepare("UPDATE item SET Name = ?, Description = ?, Price = ?, Type = ?, Availability = ?, City = ?, MapsURL = ? WHERE ItemID = ?");
$stmt->bind_param("ssdsdssi", $Name, $Description, $Price, $Type, $Availability, $City, $MapsURL, $ItemID);

// Execute the query and check for success
if ($stmt->execute()) {
    // Redirect after successful update
    header("Location: ../Equipment Inventory.php?message=Item updated successfully");
    exit;
} else {
    // Error handling for update
    echo "Error updating item: " . $stmt->error;
}

$conn->close(); // Close the database connection
?>
