<?php 
session_start(); // Start the session

// Ensure the user is logged in and the AdminID is set
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['admin_id'])) {
    header("Location: index.php"); // Redirect to login if session is invalid
    exit();
}

include 'php/db_config.php'; // Include the database connection file

// Logout functionality
if (isset($_POST['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header("Location: admin_login.php"); // Redirect to login page
    exit();
}

// Handle CRUD operations for both Edit and Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    $action = $_POST['action'];

    // Add Record
    if ($action == 'add') {
        $columns = implode(",", array_keys($_POST['data']));
        $values = implode(",", array_map(function($value) use ($conn) {
            return "'" . $conn->real_escape_string($value) . "'";
        }, $_POST['data']));
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        $conn->query($sql);
    }

    // Edit Record
    if ($action == 'edit') {
        $id = $_POST['id'];
        $updates = [];
        foreach ($_POST['data'] as $key => $value) {
            $updates[] = "$key = '" . $conn->real_escape_string($value) . "'";
        }
        $updates = implode(",", $updates);
        $sql = "UPDATE $table SET $updates WHERE id = $id"; // Assuming 'id' is a common column in each table
        $conn->query($sql);
    }

    // Delete Record
    if ($action == 'delete') {
        $id = $_POST['id'];
        $sql = "DELETE FROM $table WHERE id = $id"; // Assuming 'id' is a common column in each table
        $conn->query($sql);
    }
}

// Get table names for navigation
$tableResult = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tableResult->fetch_row()) {
    $tables[] = $row[0];
}

// Display selected table data
$table = isset($_GET['table']) ? $_GET['table'] : $tables[0];
$tableData = $conn->query("SELECT * FROM $table");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
    <link rel="stylesheet" href="css/manage.css">
</head>
<body>
    <div class="sidebar">
        <h2>Admin Dashboard</h2>
        <h3>Select a table to manage</h3>
        <?php foreach ($tables as $t): ?>
            <a href="?table=<?= $t ?>"><?= ucfirst($t) ?></a>
        <?php endforeach; ?>

        <!-- Logout Form -->
        <form action="" method="POST">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <h1>Manage <?= ucfirst($table) ?></h1>

        <!-- Table Data -->
        <table border="1">
            <thead>
                <tr>
                    <?php
                    // Dynamically create headers based on table columns
                    $columns = $conn->query("DESCRIBE $table");
                    while ($col = $columns->fetch_assoc()) {
                        echo "<th>" . ucfirst($col['Field']) . "</th>";
                    }
                    ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $tableData->fetch_assoc()): ?>
                    <tr>
                        <?php
                        foreach ($row as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        ?>
                        <td>
                            <!-- Edit Button -->
                            <a href="admin.php?table=<?= $table ?>&action=edit&id=<?= $row['id'] ?>">
                                <button>Edit</button>
                            </a>

                            <!-- Delete Button with Confirm Dialog -->
                            <a href="admin.php?table=<?= $table ?>&action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                                <button>Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Add New Record -->
        <h2>Add New <?= ucfirst($table) ?></h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="table" value="<?= $table ?>">
            <?php
            // Dynamically create input fields based on table columns
            $columns = $conn->query("DESCRIBE $table");
            while ($col = $columns->fetch_assoc()) {
                $fieldName = $col['Field'];
                echo "<label for='$fieldName'>" . ucfirst($fieldName) . ":</label>";
                echo "<input type='text' name='data[$fieldName]' required><br>";
            }
            ?>
            <button type="submit">Add Record</button>
        </form>
    </div>

    <?php
    // Handle Edit and Delete via URL parameters
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $id = $_GET['id'];

        // Edit action
        if ($action == 'edit' && isset($id)) {
            $editQuery = "SELECT * FROM $table WHERE id = ?";
            $stmt = $conn->prepare($editQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $editResult = $stmt->get_result();
            $editRecord = $editResult->fetch_assoc();
            ?>
            <div>
                <h2>Edit Record</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="table" value="<?= $table ?>">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <?php
                    foreach ($editRecord as $key => $value) {
                        if ($key != 'id') { // Exclude id field
                            echo "<label for='$key'>" . ucfirst($key) . ":</label>";
                            echo "<input type='text' name='data[$key]' value='" . htmlspecialchars($value) . "' required><br>";
                        }
                    }
                    ?>
                    <button type="submit">Update</button>
                </form>
            </div>
            <?php
        }

        // Delete action
        if ($action == 'delete' && isset($id)) {
            $deleteQuery = "DELETE FROM $table WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<p>Record deleted successfully.</p>";
                header("Location: admin.php?table=$table"); // Redirect after delete
            } else {
                echo "<p>Error deleting record: " . $conn->error . "</p>";
            }
        }
    }
    ?>

</body>
</html>

<?php $conn->close(); ?>
