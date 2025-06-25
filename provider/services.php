<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Advertising Services</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="intro-container">
        <div class="text-container">
            <h1>Welcome to Our Advertising Platform</h1>
            <p>Looking for an effective way to advertise your products or services? Our platform is the perfect solution! Whether you want to create a brand-new ad or modify an existing one, we make it quick, easy, and effective.</p>
            <p>With our platform, you can easily manage your ads, reach a wider audience, and grow your business.</p>
            <p>Choose one of the options below and get started now!</p>
        </div>

        <div class="options-container">
            <button class="option-btn" id="create-ad">Create New Ad</button>
            <button class="option-btn" id="edit-ad">Edit Existing Ad</button>
            <button class="option-btn" id="provider-profile">Provider Profile</button>
            <button class="option-btn" id="user-bookings">User Bookings</button>
        </div>

        <div class="logout-container">
            <button class="logout-btn" id="logout-btn">Logout</button>
        </div>
    </div>

    <script>
        // Redirect to the create new ad page
        document.getElementById('create-ad').addEventListener('click', function() {
            window.location.href = 'add_item.php';
        });

        // Redirect to the edit existing ad page
        document.getElementById('edit-ad').addEventListener('click', function() {
            window.location.href = 'items_list.php';
        });

        // Redirect to the provider profile page
        document.getElementById('provider-profile').addEventListener('click', function() {
            window.location.href = 'profile.php';
        });

        // Redirect to the user bookings page
        document.getElementById('user-bookings').addEventListener('click', function() {
            window.location.href = 'user_bookings.php';
        });

        // Logout functionality (redirect to logout.php to destroy the session)
        document.getElementById('logout-btn').addEventListener('click', function() {
            window.location.href = 'logout.php'; // Redirect to logout.php
        });
    </script>
</body>
</html>
