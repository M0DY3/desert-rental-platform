<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php'); // Correct path to db_config.php

$provider_id = $_SESSION['provider_id'];

// Pagination settings
$items_per_page = 6; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page number, default is 1
$offset = ($page - 1) * $items_per_page;

// Search functionality
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = '%' . $_GET['search'] . '%';
}

// Sort by Type functionality (Camp, Caravan, Equipment)
$type_sort_query = '';
if (isset($_GET['type_sort']) && !empty($_GET['type_sort'])) {
    $type_sort_query = $_GET['type_sort'];
}

// Fetch the total number of items
$query_count = "SELECT COUNT(*) FROM item WHERE ProviderID = ?";
if ($search_query) {
    $query_count .= " AND Name LIKE ?";
}
if ($type_sort_query) {
    $query_count .= " AND Type = ?";
}

$stmt_count = $conn->prepare($query_count);
if ($search_query && $type_sort_query) {
    $stmt_count->bind_param("iss", $provider_id, $search_query, $type_sort_query);
} elseif ($search_query) {
    $stmt_count->bind_param("is", $provider_id, $search_query);
} elseif ($type_sort_query) {
    $stmt_count->bind_param("is", $provider_id, $type_sort_query);
} else {
    $stmt_count->bind_param("i", $provider_id);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_items = $result_count->fetch_row()[0]; // Total items count

// Fetch the items for the current page
$query_items = "SELECT * FROM item WHERE ProviderID = ?";
if ($search_query) {
    $query_items .= " AND Name LIKE ?";
}
if ($type_sort_query) {
    $query_items .= " AND Type = ?";
}
$query_items .= " ORDER BY CreatedAt DESC LIMIT ?, ?";

$stmt_items = $conn->prepare($query_items);
if ($search_query && $type_sort_query) {
    $stmt_items->bind_param("issii", $provider_id, $search_query, $type_sort_query, $offset, $items_per_page);
} elseif ($search_query) {
    $stmt_items->bind_param("isii", $provider_id, $search_query, $offset, $items_per_page);
} elseif ($type_sort_query) {
    $stmt_items->bind_param("isii", $provider_id, $type_sort_query, $offset, $items_per_page);
} else {
    $stmt_items->bind_param("iii", $provider_id, $offset, $items_per_page);
}
$stmt_items->execute();
$result_items = $stmt_items->get_result();
$items = $result_items->fetch_all(MYSQLI_ASSOC);

// Handle delete action (only delete the item now, not the images)
if (isset($_GET['delete_item_id'])) {
    $item_id_to_delete = $_GET['delete_item_id'];

    // Delete the item from the 'item' table
    $delete_item_query = "DELETE FROM item WHERE ItemID = ? AND ProviderID = ?";
    $delete_item_stmt = $conn->prepare($delete_item_query);
    $delete_item_stmt->bind_param("ii", $item_id_to_delete, $provider_id);
    $delete_item_stmt->execute();

    // Redirect to the same page to refresh after the delete
    header('Location: items_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Items List</title>
    <link rel="stylesheet" href="stylee.css">
    <style>
        /* General layout styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
        }

        /* Pagination styling */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            background-color: #f8b500;
            color: #fff;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #f57f17;
        }

        .pagination a.active {
            background-color: #ff9800;
        }

        /* Item card layout */
        .items-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .item-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
            padding: 15px;
        }

        .item-card:hover {
            transform: scale(1.05);
        }

       

        .item-card h3 {
            font-size: 20px;
            color: #333;
        }

        .item-card p {
            color: #777;
            margin-bottom: 10px;
        }

        .item-card .price {
            font-size: 18px;
            font-weight: bold;
            color: #f57f17;
        }

        /* Buttons */
        .item-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .item-actions button {
            background-color: #f8b500;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .item-actions button:hover {
            background-color: #ff9800;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Items List</h2>

    <!-- Search and Sort Form -->
    <form method="GET" action="items_list.php">
        <input type="text" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Search Items">
        <select name="type_sort">
            <option value="">Sort by Type</option>
            <option value="Camp" <?php echo isset($_GET['type_sort']) && $_GET['type_sort'] == 'Camp' ? 'selected' : ''; ?>>Camp</option>
            <option value="Caravan" <?php echo isset($_GET['type_sort']) && $_GET['type_sort'] == 'Caravan' ? 'selected' : ''; ?>>Caravan</option>
            <option value="Equipment" <?php echo isset($_GET['type_sort']) && $_GET['type_sort'] == 'Equipment' ? 'selected' : ''; ?>>Equipment</option>
        </select>
        <button type="submit">Search & Sort</button>
    </form>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php 
        $total_pages = ceil($total_items / $items_per_page);
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a href="items_list.php?page=' . $i . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
        }
        ?>
    </div>

    <!-- Display items -->
    <div class="items-container">
        <?php foreach ($items as $item): ?>
            <div class="item-card">
                <h3><?php echo htmlspecialchars($item['Name']); ?></h3>
                <p><strong>Price:</strong> SAR <?php echo htmlspecialchars($item['Price']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($item['Type']); ?></p>
                <p class="price">SAR <?php echo htmlspecialchars($item['Price']); ?></p>

                <div class="item-actions">
                    <a href="edit_item.php?item_id=<?php echo $item['ItemID']; ?>">
                        <button>Edit Item</button>
                    </a>
                    <a href="items_list.php?delete_item_id=<?php echo $item['ItemID']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">
                        <button>Delete Item</button>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php 
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a href="items_list.php?page=' . $i . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
        }
        ?>
    </div>
</div>

</body>
</html>
