<form method="POST" action="php/admin_actions.php">
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

<?php
// Debugging echo statement
echo "<pre>";
print_r($_POST);  // Debug the POST data being sent
echo "</pre>";
?>
