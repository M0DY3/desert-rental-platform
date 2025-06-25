<?php
session_start();

// Check if item data exists in session
if (!isset($_SESSION['item_data'])) {
    header('Location: add_item.php');  // Redirect to add_item.php if session data is not set
    exit();
}

$item_data = $_SESSION['item_data'];  // Get item data from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advertisement Summary</title>
    <link rel="stylesheet" href="stylee.css">
    <style>
        /* Add any custom styles here or use your existing stylee.css */
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Advertisement Summary</h2>

        <!-- Display item details -->
        <p><strong>Ad Name:</strong> <?php echo htmlspecialchars($item_data['name']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($item_data['description']); ?></p>
        <p><strong>Price:</strong> SAR <?php echo htmlspecialchars($item_data['price']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($item_data['type']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($item_data['city']); ?></p>
        <p><strong>Maps URL:</strong> <a href="<?php echo htmlspecialchars($item_data['maps_url']); ?>" target="_blank">View on Map</a></p>

        <!-- Display images if they exist -->
        <?php if (isset($item_data['images']) && count($item_data['images']) > 0): ?>
            <p><strong>Images:</strong></p>
            <div class="images-gallery">
                <?php foreach ($item_data['images'] as $image): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Ad Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Confirm and Edit buttons -->
        <form action="confirm.php" method="POST">
            <button type="submit" name="confirm">Confirm Ad</button>
        </form>

        <form action="add_item.php" method="GET">
            <button type="submit" name="edit">Edit Ad</button>
        </form>
    </div>
</body>
</html>
