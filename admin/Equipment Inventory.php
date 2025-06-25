<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

$result = $conn->query("SELECT * FROM item");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Inventory</title>
    <link rel="stylesheet" href="css/Admin.css">
</head>
<body>
<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <a href="Current Rentals.php">Current Rentals</a>
    <a href="Equipment Inventory.php">Equipment Inventory</a>
    <a href="User Management.php">User Management</a>
    <a href="Admin.php">Admin</a>
    <a href="index.php">Home</a>

</div>

<div class="main-content">
    <h1>Equipment Inventory</h1>

    <h2>Add New Item</h2>
    <form method="POST" action="php/item_actions.php">
        <input type="hidden" name="action" value="create">
        <input name="Name" placeholder="Name" required>
        <input name="Description" placeholder="Description" required>
        <input name="Price" type="number" step="5" placeholder="Price" required>
        <input name="Type" placeholder="Type" required>
        <input name="Availability" placeholder="Availability" required>
        <input name="City" placeholder="City">
        <input name="MapsURL" placeholder="Maps URL">
        <button type="submit">Add Item</button>
    </form>

    <h2>All Items</h2>
    <table>
        <tr>
            <th>ItemID</th><th>Name</th><th>Description</th><th>Price</th><th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['ItemID']) ?></td>
                <td><?= htmlspecialchars($row['Name']) ?></td>
                <td><?= htmlspecialchars($row['Description']) ?></td>
                <td><?= htmlspecialchars($row['Price']) ?></td>
                <td>
                    <form method="POST" action="php/item_actions.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ItemID" value="<?= $row['ItemID'] ?>">
                        <button type="submit" onclick="return confirm('Delete this item?')">Delete</button>
                    </form>
                    <form method="GET" action="php/edit_item.php" style="display:inline;">
                        <input type="hidden" name="ItemID" value="<?= $row['ItemID'] ?>">
                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>
