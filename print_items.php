<?php   
session_start(); // Start session to check if user is logged in
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

// Filter handling
$type_filter = $_GET['type'] ?? '';
$price_filter = isset($_GET['price']) ? (float)$_GET['price'] : 0;
$city_filter = $_GET['city'] ?? ''; // Added city filter

// Query items
$sql = "
    SELECT i.ItemID, i.ProviderID, i.Name, i.Description, i.Price, i.Type, i.Availability,
           i.CreatedAt, i.Latitude, i.Longitude, i.City, i.MapsURL, img.ImageURL
    FROM item i
    LEFT JOIN img img ON i.ItemID = img.ItemID
    WHERE (? = '' OR i.Type = ?)
      AND (i.Price <= ? OR ? = 0)
      AND (? = '' OR i.City = ?)
    ORDER BY i.ItemID ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssddss', $type_filter, $type_filter, $price_filter, $price_filter, $city_filter, $city_filter);
$stmt->execute();
$result = $stmt->get_result();

// Group items by ID
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
    <title><?= htmlspecialchars($type_filter) ?: 'All Items' ?> - Rental Items</title>
    <link rel="stylesheet" href="css/RentalServices.css">
    <style>
        body {
            background-color: #f4f4f9;
            color: #333;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #444;
            padding: 20px;
            text-align: center;
        }

        header img {
            width: 150px;
            height: auto;
        }

        h1 {
            font-size: 2.5em;
            margin: 10px 0;
            color: white;
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .image-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }

        .item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: calc(33% - 20px);
            padding: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 20px;
        }

        .item:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .item h3 {
            font-size: 1.5em;
            color: #333;
            margin: 10px 0;
        }

        .item p {
            color: #555;
            margin-bottom: 10px;
        }

        .item .price {
            font-size: 1.2em;
            color: #28a745;
            margin-bottom: 10px;
        }

        .item .type, .item .region { /* Changed class name from city to region */
            font-size: 1em;
            color: #666;
        }

        .item .maps-link {
            color: #007bff;
            text-decoration: none;
        }

        .item-image {
            width: 100%;
            height: 250px; /* Standardized image size */
            border-radius: 8px;
            margin-bottom: 15px;
            object-fit: cover;
        }

        .add-to-basket-btn {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            border: none;
        }

        .add-to-basket-btn:hover {
            background-color: #218838;
        }

        .basket-button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .basket-button:hover {
            background-color: #218838;
        }

        footer {
            background-color: #444;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #333;
            text-align: center;
        }

        nav li {
            display: inline-block;
        }

        nav a {
            color: white;
            padding: 10px 15px;
            display: inline-block;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #575757;
        }

        form select, form input[type="number"], form button {
            padding: 8px;
            border-radius: 5px;
            margin: 10px 5px 20px;
        }

        @media (max-width: 768px) {
            .item { width: calc(50% - 20px); }
        }

        @media (max-width: 480px) {
            .item { width: 100%; }
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
        <li><a href="rental_services.php">Services</a></li>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <li><a href="account.php">Account</a></li>
            <li><a href="basket.php">My Basket</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main class="container">
    <h2>Available Items</h2>

    <form method="GET" action="">
        <label for="type">Type:</label>
        <select name="type" id="type">
            <option value="" <?= $type_filter === '' ? 'selected' : '' ?>>All</option>
            <option value="Camp" <?= $type_filter === 'Camp' ? 'selected' : '' ?>>Camp</option>
            <option value="Caravan" <?= $type_filter === 'Caravan' ? 'selected' : '' ?>>Caravan</option>
            <option value="Equipment" <?= $type_filter === 'Equipment' ? 'selected' : '' ?>>Equipment</option>
        </select>

        <label for="price">Max Price:</label>
        <input type="number" name="price" id="price" value="<?= htmlspecialchars($price_filter) ?>" placeholder="Enter max price" />

        <!-- Add city filter -->
        <label for="city">Region:</label>
        <select name="city" id="city">
            <option value="" <?= $city_filter === '' ? 'selected' : '' ?>>All Regions</option>
            <option value="Riyadh" <?= $city_filter === 'Riyadh' ? 'selected' : '' ?>>Riyadh</option>
            <option value="Jeddah" <?= $city_filter === 'Jeddah' ? 'selected' : '' ?>>Jeddah</option>
            <option value="Makkah" <?= $city_filter === 'Makkah' ? 'selected' : '' ?>>Makkah</option>
            <option value="Medina" <?= $city_filter === 'Medina' ? 'selected' : '' ?>>Medina</option>
            <option value="Khobar" <?= $city_filter === 'Khobar' ? 'selected' : '' ?>>Khobar</option>
            <option value="Abha" <?= $city_filter === 'Abha' ? 'selected' : '' ?>>Abha</option>
            <option value="Tabuk" <?= $city_filter === 'Tabuk' ? 'selected' : '' ?>>Tabuk</option>
            <option value="Dammam" <?= $city_filter === 'Dammam' ? 'selected' : '' ?>>Dammam</option>
            <option value="Al Ula" <?= $city_filter === 'Al Ula' ? 'selected' : '' ?>>Al Ula</option>
            <option value="Al Khobar" <?= $city_filter === 'Al Khobar' ? 'selected' : '' ?>>Al Khobar</option>
            <option value="NEOM" <?= $city_filter === 'NEOM' ? 'selected' : '' ?>>NEOM</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="image-container">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $grouped_item): ?>
                <?php $item = $grouped_item['item']; ?>
                <div class="item">
                    <h3><a href="item_detail.php?item_id=<?= $item['ItemID'] ?>"><?= htmlspecialchars($item['Name']) ?></a></h3>
                    <p><?= htmlspecialchars($item['Description']) ?></p>
                    <p class="price">Price: $<?= htmlspecialchars($item['Price']) ?></p>
                    <p class="type">Type: <?= htmlspecialchars($item['Type']) ?></p>
                    <p class="region">Region: <a href="<?= htmlspecialchars($item['MapsURL']) ?>" class="maps-link" target="_blank"><?= htmlspecialchars($item['City']) ?></a></p>

                    <div class="image-container">
                        <img id="item-image-<?= $item['ItemID'] ?>" src="<?= htmlspecialchars($grouped_item['images'][0]) ?>" alt="Item Image" class="item-image">
                    </div>

                    <script>
                        let images = <?= json_encode($grouped_item['images']) ?>;
                        let imageIndex = 0;

                        setInterval(function() {
                            document.getElementById('item-image-<?= $item['ItemID'] ?>').src = images[imageIndex];
                            imageIndex = (imageIndex + 1) % images.length;
                        }, 5000);
                    </script>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No items found matching your criteria.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>
