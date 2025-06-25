<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

include 'php/db_config.php'; // Correct path

// Check if 'table' and 'id' are set in the URL
if (isset($_GET['table']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];

    // Get the primary key for the specified table
    $primaryKey = getPrimaryKey($table);

    // Prepare and execute the delete query
    if ($primaryKey) {
        $sql = "DELETE FROM $table WHERE $primaryKey = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Successful delete, redirect back to manage page
            header("Location: admin.php?table=$table");
            exit();
        } else {
            // Error in deletion
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Error: Could not get the primary key for the table $table.";
    }
} else {
    echo "Invalid request. No table or ID specified.";
}

// Get the primary key dynamically for the specified table
function getPrimaryKey($table) {
    global $conn;
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        if ($row['Key'] == 'PRI') {
            return $row['Field'];
        }
    }
    return null;
}

$conn->close();
?>
