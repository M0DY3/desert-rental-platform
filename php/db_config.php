<?php
// db_config.php

$servername = "localhost"; // Database server, usually localhost
$username = "root"; // Database username, often 'root' for local development
$password = ""; // Database password, leave empty if using XAMPP default
$dbname = "desert_rental_platform"; // Your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // Optional: For debugging, confirm the connection works.
    // echo "Connected successfully";
}
?>
