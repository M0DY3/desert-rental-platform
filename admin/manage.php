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
    header("Location: index.php"); // Redirect to login page
    exit();
}

// Handle Search functionality
$searchQuery = isset($_POST['search']) ? $_POST['search'] : '';

// Get table names for navigation
$tableResult = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tableResult->fetch_row()) {
    $tables[] = $row[0];
}

// Display selected table data
$table = isset($_GET['table']) ? $_GET['table'] : $tables[0];

// Get columns for the selected table
$columnsResult = $conn->query("DESCRIBE $table");
$columns = [];
while ($col = $columnsResult->fetch_assoc()) {
    $columns[] = $col['Field'];
}

// Handle Search query if exists
if ($searchQuery) {
    $searchColumns = implode(" LIKE '%$searchQuery%' OR ", $columns) . " LIKE '%$searchQuery%'";
    $sql = "SELECT * FROM $table WHERE $searchColumns";
    $tableData = $conn->query($sql);
} else {
    $tableData = $conn->query("SELECT * FROM $table");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage <?= ucfirst($table) ?></title>
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

        <!-- Search Form -->
        <form method="POST">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Table Data -->
        <table border="1">
            <thead>
                <tr>
                    <?php
                    // Dynamically create headers based on table columns
                    foreach ($columns as $col) {
                        echo "<th>" . ucfirst($col) . "</th>";
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
                            <form action="php/edit.php" method="GET" style="display:inline;">
                                <input type="hidden" name="table" value="<?= $table ?>">
                                <input type="hidden" name="id" value="<?= $row[getPrimaryKey($table)] ?>">
                                <button type="submit">Edit</button>
                            </form>

                            <!-- Delete Form with Confirmation -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="table" value="<?= $table ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $row[getPrimaryKey($table)] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this record?');">Delete</button>
                            </form>
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
            $columnsResult = $conn->query("DESCRIBE $table");
            while ($col = $columnsResult->fetch_assoc()) {
                $fieldName = $col['Field'];
                echo "<label for='$fieldName'>" . ucfirst($fieldName) . ":</label>";
                echo "<input type='text' name='data[$fieldName]' required><br>";
            }
            ?>
            <button type="submit">Add Record</button>
        </form>
    </div>

    <script>
        // Add any custom JavaScript if necessary
    </script>
</body>
</html>

<?php
// Function to get the primary key dynamically
function getPrimaryKey($table) {
    global $conn;
    $result = $conn->query("DESCRIBE $table");
    while ($row = $result->fetch_assoc()) {
        if ($row['Key'] == 'PRI') {
            return $row['Field']; // Return the primary key column
        }
    }
    return null;
}

$conn->close();
?>
