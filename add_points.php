<?php
session_start();
require 'php/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id']) && isset($_POST['points'])) {
        $user_id = $_POST['user_id'];
        $points = (int) $_POST['points'];

        // Check if the user already has loyalty points
        $stmt = $conn->prepare("SELECT Points FROM loyaltyprogram WHERE UserID = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User already has loyalty points, add new points
            $row = $result->fetch_assoc();
            $newPoints = $row['Points'] + $points;

            $stmt = $conn->prepare("UPDATE loyaltyprogram SET Points = ?, LastUpdated = NOW() WHERE UserID = ?");
            $stmt->bind_param('ii', $newPoints, $user_id);
        } else {
            // User doesn't have any loyalty points, insert a new record
            $stmt = $conn->prepare("INSERT INTO loyaltyprogram (UserID, Points, LastUpdated) VALUES (?, ?, NOW())");
            $stmt->bind_param('ii', $user_id, $points);
        }

        if ($stmt->execute()) {
            echo 'Points updated successfully';
        } else {
            echo 'Error updating points';
        }
    }
}
?>
