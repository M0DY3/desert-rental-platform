<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'php/db_config.php'; // Ensure this path is correct

// Initialize filters
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$price_filter = isset($_GET['price']) ? (float)$_GET['price'] : 0;

// Prepare SQL query to retrieve items
$sql = "SELECT i.ItemID, i.ProviderID, i.Name, i.Description, i.Price, i.Type, i.Availability, 
               i.CreatedAt, i.Latitude, i.Longitude, i.City, i.MapsURL, img.ImageURL 
        FROM item i 
        LEFT JOIN img img ON i.ItemID = img.ItemID 
        WHERE (? = '' OR i.Type = ?) 
        AND (i.Price <= ? OR ? = 0)
        ORDER BY i.ItemID ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param('ssdd', $type_filter, $type_filter, $price_filter, $price_filter);
$stmt->execute();
$result = $stmt->get_result();
if ($result === false) {
    die("Execute failed: " . $stmt->error);
}

// Create an array to hold items
$items = [];
while ($item = $result->fetch_assoc()) {
    $items[$item['ItemID']]['item'] = $item;
    $items[$item['ItemID']]['images'][] = $item['ImageURL'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items <?= htmlspecialchars($type_filter) ?: 'All Items' ?></title>
    <link rel="stylesheet" href="css/Rental Services.css">
    <style>
        .image-container {
            display: flex;
            flex-wrap: wrap;
        }

        .item-image {
            width: 100px; /* Adjust size as needed */
            height: auto; /* Maintain aspect ratio */
            margin: 5px; /* Space between images */
        }

        .add-to-basket {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<header>  
    <img src="images/logo.png" alt="logo">
    <h1><?= htmlspecialchars($type_filter) ?: 'All Items' ?></h1>
</header>

<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="rental_services.html">Services</a></li>
        <li><a href="Login.php">Login</a></li>
        <li><a href="signup.php">Signup</a></li>
    </ul>
</nav>

<main>
    <div class="container">
        <h2>Items</h2>
        <form method="GET" action="">
            <label for="type">Type:</label>
            <select name="type" id="type">
                <option value="" <?= $type_filter === '' ? 'selected' : '' ?>>All</option>
                <option value="Camp" <?= $type_filter === 'Camp' ? 'selected' : '' ?>>Camp</option>
                <option value="Caravan" <?= $type_filter === 'Caravan' ? 'selected' : '' ?>>Caravan</option>
                <option value="Equipment" <?= $type_filter === 'Equipment' ? 'selected' : '' ?>>Equipment</option>
            </select>

            <label for="price">Max Price:</label>
            <input type="number" name="price" id="price" placeholder="Enter max price" value="<?= htmlspecialchars($price_filter) ?>" />
            <button type="submit">Filter</button>
        </form>

        <ul class="service-list">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $grouped_item): ?>
                    <?php $item = $grouped_item['item']; ?>
                    <li class="option">
                        <h3><?= htmlspecialchars($item['Name']) ?></h3>
                        <p><?= htmlspecialchars($item['Description']) ?></p>
                        <p>Price: $<?= htmlspecialchars($item['Price']) ?></p>
                        <p>Type: <?= htmlspecialchars($item['Type']) ?></p>
                        <p>Location: <a href="<?= htmlspecialchars($item['MapsURL']) ?>" target="_blank"><?= htmlspecialchars($item['City']) ?></a></p>
                        <div class="logo">
                            <?php if (!empty($grouped_item['images'])): ?>
                                <div class="image-container">
                                    <?php foreach ($grouped_item['images'] as $image): ?>
                                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['Name']) ?>" class="item-image">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No image available</p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="add_to_basket.php" class="add-to-basket">
                            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['ItemID']) ?>">
                            <button type="submit">Add to Basket</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No items found matching your criteria.</li>
            <?php endif; ?>
        </ul>
    </div>
</main>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>
</body>
</html>