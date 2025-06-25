<?php
include 'db_config.php';

// Get AdminID from the query string (URL)
$AdminID = $_GET['AdminID'] ?? null;

// If AdminID is missing, show an error
if (!$AdminID) {
    die("Invalid request - AdminID is missing.");
}

// Prepare and execute the query to fetch the admin details
$stmt = $conn->prepare("SELECT * FROM admin WHERE AdminID = ?");
$stmt->bind_param("i", $AdminID);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// If no admin is found, show an error
if (!$admin) {
    die("Admin not found.");
}
?>

<form method="POST" action="admin_actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="AdminID" value="<?= htmlspecialchars($admin['AdminID']) ?>">
    
    <label for="Username">Username:</label>
    <input name="Username" value="<?= htmlspecialchars($admin['Username']) ?>" required>
    
    <label for="Email">Email:</label>
    <input name="Email" value="<?= htmlspecialchars($admin['Email']) ?>" required>
    
    <label for="Password">Password:</label>
    <input name="Password" type="password" value="<?= htmlspecialchars($admin['Password']) ?>" required>
    
    <button type="submit">Update Admin</button>
</form>


