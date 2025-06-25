<?php
include 'php/db_config.php';

if (isset($_GET['phone'])) {
    $phone = $_GET['phone'];

    // Query to check if the phone number already exists
    $sql = "SELECT * FROM Users WHERE Phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    // Return JSON response
    echo json_encode(['exists' => $result->num_rows > 0]);
}
?>
