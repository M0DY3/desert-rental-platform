<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "desert_rental_platform";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Desert Rental Platform</title>
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
    <h1>User Management</h1>
    <?php
    // Hardcoded users data
    $users = [
        ["UserID" => 2, "Name" => "mody", "Email" => "mody@mody.com"],
        ["UserID" => 3, "Name" => "firas", "Email" => "firas@firas.com"],
        ["UserID" => 5, "Name" => "a", "Email" => "firasalraddadi@gmail.com"],
    ];

    if (count($users) > 0) {
        echo "<table><tr><th>UserID</th><th>Name</th><th>Email</th></tr>";
        foreach ($users as $user) {
            echo "<tr>
                    <td>{$user['UserID']}</td>
                    <td>{$user['Name']}</td>
                    <td>{$user['Email']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No users found.";
    }
    $conn->close();
    ?>
</div>

</body>
</html>