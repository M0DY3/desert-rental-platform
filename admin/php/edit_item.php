<?php
include 'db_config.php';
$ItemID = $_GET['ItemID'] ?? null;
if (!$ItemID) die("Invalid request");

$stmt = $conn->prepare("SELECT * FROM item WHERE ItemID = ?");
$stmt->bind_param("i", $ItemID);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
?>

<form method="POST" action="item_actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="ItemID" value="<?= $item['ItemID'] ?>">
    <input name="Name" value="<?= htmlspecialchars($item['Name']) ?>" required>
    <input name="Description" value="<?= htmlspecialchars($item['Description']) ?>" required>
    <input name="Price" value="<?= htmlspecialchars($item['Price']) ?>" type="number" step="0.01" required>
    <input name="Type" value="<?= htmlspecialchars($item['Type']) ?>" required>
    <input name="Availability" value="<?= htmlspecialchars($item['Availability']) ?>" required>
    <input name="Latitude" value="<?= htmlspecialchars($item['Latitude']) ?>">
    <input name="Longitude" value="<?= htmlspecialchars($item['Longitude']) ?>">
    <input name="City" value="<?= htmlspecialchars($item['City']) ?>">
    <input name="MapsURL" value="<?= htmlspecialchars($item['MapsURL']) ?>">
    <button type="submit">Update</button>
</form>
