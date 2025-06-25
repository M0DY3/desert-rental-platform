<?php
session_start(); // Start session to check if user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Services</title>
    <link rel="stylesheet" href="css/Rental Services.css">
</head>
<body id="rental-services-page">
<header>  
    <img src="images/logo.png" alt="logo">
    <h1>Rental Services</h1>
</header>

<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="rental_services.php">Services</a></li>

        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- User is logged in -->
            <li><a href="account.php">Account</a></li>
            <li><a href="basket.php">My Basket</a></li>
        <?php else: ?>
            <!-- User is not logged in -->
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main>
    <div class="container">
        <h2>Our Rental Services</h2>
        <ul class="service-list">
            <li class="option">
                <a href="print_items.php?type=Camp" class="service-button">Camp</a>
                <div class="logo">
                    <img src="images/image 6.png" alt="Camping Logo">
                </div>
            </li>
            <li class="option">
                <a href="print_items.php?type=Caravan" class="service-button">Caravan</a>
                <div class="logo">
                    <img src="images/image 7.png" alt="Camping Logo">
                </div>
            </li>
            <li class="option">
                <a href="print_items.php?type=Equipment" class="service-button">Equipment</a>
                <div class="logo">
                    <img src="images/image 8.png" alt="Camping Logo">
                </div>
            </li>
        </ul>
    </div>
</main>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>
</body>
</html>