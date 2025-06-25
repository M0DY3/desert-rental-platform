<?php
session_start(); // Start session to check if user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camping Adventures</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body id="home-page">

<header>  
    <img src="images/logo.png" alt="logo">
    <h1>Camping Adventures</h1>
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
        <?php else: ?>
            <!-- User is not logged in -->
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main>
    <section class="service">
        <h2>Our Services</h2>
        <p>Rent desert camps, caravans, and outdoor equipment for an unforgettable adventure in Saudi Arabia. 
            Whether you're looking for a peaceful getaway or an exciting desert journey, 
            we provide everything you need for a memorable outdoor experience.</p>
        <div class="service-options">
            <button><a href="print_items.php">Explore</a></button>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Camping Adventures. All Rights Reserved.</p>
</footer>
</body>
</html>
