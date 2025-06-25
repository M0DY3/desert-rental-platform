<?php
include 'php/db_config.php';

$bookingResult = $conn->query("SELECT * FROM booking");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Current Rentals</title>
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
    <h1>Current Bookings</h1>

    <h2>All Bookings</h2>
    <table>
        <tr>
            <th>BookingID</th>
            <th>UserID</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Total Price</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $bookingResult->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['BookingID']) ?></td>
                <td><?= htmlspecialchars($row['UserID']) ?></td>
                <td><?= htmlspecialchars($row['StartDate']) ?></td>
                <td><?= htmlspecialchars($row['EndDate']) ?></td>
                <td><?= htmlspecialchars($row['TotalPrice']) ?></td>
                <td>
                    <form method="POST" action="php/booking_actions.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
                        <button type="submit" onclick="return confirm('Delete this booking?')">Delete</button>
                    </form>
                    <form method="GET" action="php/edit_booking.php" style="display:inline;">
                        <input type="hidden" name="BookingID" value="<?= $row['BookingID'] ?>">
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
