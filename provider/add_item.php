<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php');  // Correct path to db_config.php

// Cities list in KSA (for the dropdown)
$cities = [
    "Riyadh", "Jeddah", "Makkah", "Medina", "Khobar", "Abha", "Tabuk", "Dammam", "Al Ula", "Al Khobar", "NEOM"
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $provider_id = $_SESSION['provider_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $city = $_POST['city'];  // Get the selected city
    $maps_url = $_POST['maps_url'];

    // Insert item data into the database
    $query = "INSERT INTO item (ProviderID, Name, Description, Price, Type, City, MapsURL, CreatedAt) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Check if the prepare statement was successful
    if ($stmt === false) {
        die('Error preparing the SQL query: ' . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param("issdsss", $provider_id, $name, $description, $price, $type, $city, $maps_url);

    // Check if execute() was successful
    if (!$stmt->execute()) {
        die('Error executing the query: ' . $stmt->error);
    }

    // Get the last inserted item ID and store it in session
    $item_id = $conn->insert_id;
    $_SESSION['item_id'] = $item_id; // Store the ItemID in session

    // Handle image upload
    $upload_dir = '../images/item/'; // Set the relative path for images
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $image_paths = [];

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        for ($i = 0; $i < min(5, count($_FILES['images']['name'])); $i++) {
            $image_tmp_path = $_FILES['images']['tmp_name'][$i];
            $image_name = $_FILES['images']['name'][$i];
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);

            // Check if the image extension is valid
            if (!in_array(strtolower($image_extension), $allowed_extensions)) {
                echo "Error: Invalid image format.";
                exit();
            }

            // Generate unique name for the image
            $image_new_name = uniqid($name . '_') . '.' . $image_extension;
            $image_upload_path = $upload_dir . $image_new_name;

            // Move the image to the desired directory
            if (move_uploaded_file($image_tmp_path, $image_upload_path)) {
                $image_paths[] = $image_upload_path;

                // Insert image info into the database (relative path)
                $relative_image_path = 'images/item/' . $image_new_name;

                $query = "INSERT INTO img (ItemID, ImageURL, CreatedAt) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $item_id, $relative_image_path);
                $stmt->execute();
            } else {
                echo "Error: Failed to upload image.";
                exit();
            }
        }
    }

    // Store the item data in session to display on summary page
    $_SESSION['item_data'] = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'type' => $type,
        'city' => $city,
        'maps_url' => $maps_url,
        'images' => $image_paths
    ];

    header('Location: summary.php');  // Redirect to summary page after successful item creation
    exit();
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Item</title>
  <link rel="stylesheet" href="stylee.css" />
</head>
<body>
  <div class="form-container">
    <h2>Add New Advertisement</h2>
    <form method="POST" action="add_item.php" enctype="multipart/form-data">
      <label for="name">Item Name:</label>
      <input type="text" id="name" name="name" required />

      <label for="description">Description:</label>
      <textarea id="description" name="description" required></textarea>

      <label for="price">Price (SAR):</label>
      <input type="number" step="0.01" id="price" name="price" required />

      <label for="type">Type:</label>
      <select id="type" name="type" required>
        <option value="Camp">Camp</option>
        <option value="Caravan">Caravan</option>
        <option value="Equipment">Equipment</option>
      </select>

      <label for="city">City:</label>
      <select id="city" name="city" required>
        <?php foreach ($cities as $city_option): ?>
          <option value="<?= $city_option ?>"><?= $city_option ?></option>
        <?php endforeach; ?>
      </select>

      <label for="maps_url">Google Maps URL:</label>
      <input type="url" id="maps_url" name="maps_url" required />

      <label for="images">Upload Images (Max 5):</label>
      <input type="file" id="images" name="images[]" accept="image/*" multiple required />

      <button type="submit">Add Item</button>
    </form>
  </div>
</body>
</html>
