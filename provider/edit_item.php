<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php'); // Correct path to db_config.php

// Provider's ID
$provider_id = $_SESSION['provider_id'];

// Handle item edit if item_id is passed
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    // Fetch item details from database
    $query = "SELECT * FROM item WHERE ItemID = ? AND ProviderID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $item_id, $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        echo "Item not found!";
        exit();
    }

    // Fetch images related to the item
    $query_images = "SELECT * FROM img WHERE ItemID = ?";
    $stmt_images = $conn->prepare($query_images);
    $stmt_images->bind_param("i", $item_id);
    $stmt_images->execute();
    $result_images = $stmt_images->get_result();
    $images = $result_images->fetch_all(MYSQLI_ASSOC);

    // Handle form submission for editing item
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $type = $_POST['type'];
        $city = $_POST['city'];
        $maps_url = $_POST['maps_url'];

        // Update item details in the database
        $query = "UPDATE item SET Name = ?, Description = ?, Price = ?, Type = ?, City = ?, MapsURL = ? WHERE ItemID = ? AND ProviderID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssdsdsi", $name, $description, $price, $type, $city, $maps_url, $item_id, $provider_id);
        $stmt->execute();

        // Handle image upload if any new images are uploaded
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $upload_dir = '../images/item/';
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $new_images = [];
            $max_images = 5;

            // Check how many images the item currently has
            $existing_images_count = count($images);
            $remaining_images_count = $max_images - $existing_images_count;

            if ($remaining_images_count > 0) {
                // Loop through the uploaded images and save them
                for ($i = 0; $i < min(count($_FILES['images']['name']), $remaining_images_count); $i++) {
                    $image_tmp_path = $_FILES['images']['tmp_name'][$i];
                    $image_name = $_FILES['images']['name'][$i];
                    $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

                    // Check if the image extension is valid
                    if (in_array(strtolower($image_extension), $allowed_extensions)) {
                        // Generate unique name for the image
                        $image_new_name = uniqid($name . '_') . '.' . $image_extension;
                        $image_upload_path = $upload_dir . $image_new_name;

                        // Move the image to the desired directory
                        if (move_uploaded_file($image_tmp_path, $image_upload_path)) {
                            $new_images[] = $image_upload_path;

                            // Insert image info into the database
                            $relative_image_path = 'images/item/' . $image_new_name;
                            $query_image = "INSERT INTO img (ItemID, ImageURL, CreatedAt) VALUES (?, ?, NOW())";
                            $stmt_image = $conn->prepare($query_image);
                            $stmt_image->bind_param("is", $item_id, $relative_image_path);
                            $stmt_image->execute();
                        }
                    }
                }
            }
        }

        header('Location: items_list.php'); // Redirect to the list of items after editing
        exit();
    }

    // Handle image delete action
    if (isset($_GET['delete_image_id'])) {
        $image_id_to_delete = $_GET['delete_image_id'];

        // Get the image file path from the database
        $query_image_path = "SELECT ImageURL FROM img WHERE ImageID = ?";
        $stmt_image_path = $conn->prepare($query_image_path);
        $stmt_image_path->bind_param("i", $image_id_to_delete);
        $stmt_image_path->execute();
        $result_image_path = $stmt_image_path->get_result();
        $image_data = $result_image_path->fetch_assoc();

        if ($image_data) {
            // Delete the image from the img table
            $query_delete_image = "DELETE FROM img WHERE ImageID = ?";
            $stmt_delete_image = $conn->prepare($query_delete_image);
            $stmt_delete_image->bind_param("i", $image_id_to_delete);
            $stmt_delete_image->execute();

            // Delete the image file from the server
            unlink('../' . $image_data['ImageURL']);
        }

        // Redirect to the same page after image deletion
        header('Location: edit_item.php?item_id=' . $item_id);
        exit();
    }
} else {
    echo "Invalid Item ID!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="form-container">
        <h2>Edit Item</h2>
        <form method="POST" action="edit_item.php?item_id=<?php echo $item['ItemID']; ?>" enctype="multipart/form-data">
            <label for="name">Item Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($item['Name']); ?>" required />

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($item['Description']); ?></textarea>

            <label for="price">Price (SAR):</label>
            <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($item['Price']); ?>" required />

            <label for="type">Type:</label>
            <select id="type" name="type" required>
                <option value="Camp" <?php echo ($item['Type'] == 'Camp') ? 'selected' : ''; ?>>Camp</option>
                <option value="Caravan" <?php echo ($item['Type'] == 'Caravan') ? 'selected' : ''; ?>>Caravan</option>
                <option value="Equipment" <?php echo ($item['Type'] == 'Equipment') ? 'selected' : ''; ?>>Equipment</option>
            </select>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($item['City']); ?>" required />

            <label for="maps_url">Google Maps URL:</label>
            <input type="url" id="maps_url" name="maps_url" value="<?php echo htmlspecialchars($item['MapsURL']); ?>" required />

            <label for="images">Upload New Images (Max 5):</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple />

            <button type="submit">Update Item</button>
        </form>

        <h3>Existing Images</h3>
        <div class="existing-images">
            <?php foreach ($images as $image): ?>
                <div class="image-container">
                    <img src="../<?php echo $image['ImageURL']; ?>" alt="Item Image" width="100" height="100">
                    <a href="edit_item.php?item_id=<?php echo $item['ItemID']; ?>&delete_image_id=<?php echo $image['ImageID']; ?>" onclick="return confirm('Are you sure you want to delete this image?');">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
