<?php
include 'db_config.php';

// Get the action type (update, delete, etc.)
$action = $_POST['action'] ?? '';
if (!$action) {
    die("Invalid request - no action specified.");
}

switch ($action) {
    case 'update':
        // Get the form data
        $AdminID = $_POST['AdminID'] ?? null;
        $Username = $_POST['Username'] ?? null;
        $Email = $_POST['Email'] ?? null;
        $Password = $_POST['Password'] ?? null;

        // Check if all required fields are present
        if (!$AdminID || !$Username || !$Email || !$Password) {
            die("Missing required fields.");
        }

        // Prepare the update query
        $stmt = $conn->prepare("UPDATE admin SET Username = ?, Email = ?, Password = ? WHERE AdminID = ?");
        $stmt->bind_param("sssi", $Username, $Email, $Password, $AdminID);

        // Execute the query and check for success
        if ($stmt->execute()) {
            // Redirect after successful update
            header("Location: ../Admin.php?message=Admin updated successfully");
            exit;
        } else {
            // Error handling for update
            echo "Error updating admin: " . $stmt->error;
        }
        break;

    case 'create':
        // Handle admin creation (you already have this working)
        break;

    case 'delete':
        // Handle admin deletion (you already have this working)
        break;

    default:
        echo "Invalid action specified.";
        break;
}

// Close the database connection
$conn->close();
?>
