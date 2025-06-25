<?php
include 'db_config.php';

$query = "SELECT * FROM services";
$result = $conn->query($query);
$services = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>
