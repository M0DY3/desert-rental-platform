<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

include '../php/db_config.php'; // Adjusted path

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';

// Function to get primary key dynamically
function getPrimaryKey($table) {
    global $conn;
    $result = $conn->query("DESCRIBE `$table`");
    while ($row = $result->fetch_assoc()) {
        if ($row['Key'] === 'PRI') {
            return $row['Field'];
        }
    }
    return null;
}

$primaryKey = getPrimaryKey($table);

// Fetch record
$sql = "SELECT * FROM `$table` WHERE `$primaryKey` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateFields = [];
    $values = [];
    foreach ($_POST['data'] as $key => $value) {
        $updateFields[] = "`$key` = ?";
        $values[] = $value;
    }

    $values[] = $id;
    $sql = "UPDATE `$table` SET " . implode(", ", $updateFields) . " WHERE `$primaryKey` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($values) - 1) . 'i', ...$values);
    $stmt->execute();

    header("Location: ../manage.php?table=$table");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?= htmlspecialchars($table) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }

        h1 {
            text-align: center;
        }

        form {
            background: #fff;
            padding: 20px;
            margin: 0 auto;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .btn-group {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        button, a.cancel-btn {
            padding: 10px 20px;
            border: none;
            color: #fff;
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button {
            background-color: #4CAF50;
        }

        a.cancel-btn {
            background-color: #f44336;
            text-align: center;
        }

        a.cancel-btn:hover,
        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<h1>Edit <?= ucfirst($table) ?> Record</h1>

<form method="POST">
    <?php
    $columns = $conn->query("DESCRIBE `$table`");
    while ($column = $columns->fetch_assoc()) {
        $field = $column['Field'];
        if ($field === $primaryKey) continue;

        $type = strtolower($column['Type']);
        $inputType = 'text';

        if (strpos($type, 'int') !== false) $inputType = 'number';
        if (strpos($type, 'date') !== false) $inputType = 'date';
        if (strpos($type, 'email') !== false) $inputType = 'email';

        $value = htmlspecialchars($record[$field]);

        echo "<label for='$field'>" . ucfirst($field) . "</label>";
        echo "<input type='$inputType' name='data[$field]' id='$field' value='$value' required>";
    }
    ?>

    <div class="btn-group">
        <button type="submit">Update Record</button>
        <a class="cancel-btn" href="../manage.php?table=<?= urlencode($table) ?>">Cancel</a>
    </div>
</form>

</body>
</html>

<?php $conn->close(); ?>
