<?php
include 'php/db_config.php';

// Get ItemID from the query string (URL)
$ItemID = $_GET['ItemID'] ?? null;

// If ItemID is missing, show an error
if (!$ItemID) {
    die("Invalid request - ItemID is missing.");
}

// Prepare and execute the query to fetch the item details
$stmt = $conn->prepare("SELECT * FROM item WHERE ItemID = ?");
$stmt->bind_param("i", $ItemID);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// If no item is found, show an error
if (!$item) {
    die("Item not found.");
}
?>

<!-- Form to Edit Item -->
<form method="POST" action="php/item_actions.php">
    <input type="hidden" name="action" value="update"> <!-- Action type to specify this is an update -->
    <input type="hidden" name="ItemID" value="<?= htmlspecialchars($item['ItemID']) ?>"> <!-- Hidden ItemID for identification -->

    <label for="Name">Name:</label>
    <input name="Name" value="<?= htmlspecialchars($item['Name']) ?>" required>

    <label for="Description">Description:</label>
    <input name="Description" value="<?= htmlspecialchars($item['Description']) ?>" required>

    <label for="Price">Price:</label>
    <input name="Price" value="<?= htmlspecialchars($item['Price']) ?>" required>

    <label for="Type">Type:</label>
    <input name="Type" value="<?= htmlspecialchars($item['Type']) ?>" required>

    <label for="Availability">Availability:</label>
    <input name="Availability" value="<?= htmlspecialchars($item['Availability']) ?>" required>

    <label for="City">City:</label>
    <input name="City" value="<?= htmlspecialchars($item['City']) ?>">

    <label for="MapsURL">Maps URL:</label>
    <input name="MapsURL" value="<?= htmlspecialchars($item['MapsURL']) ?>">

    <button type="submit">Update Item</button>
</form>

<?php
$conn->close(); // Close the database connection
?>
