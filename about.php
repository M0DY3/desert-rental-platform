<?php
session_start(); // Start session to check if user is logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Camping Adventures</title>
    <link rel="stylesheet" href="css/about.css">
</head>
<body>
<header>
    <img src="images/logo.png" alt="Camping Adventures Logo">
    <h1>About Us</h1>
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
    <section class="about">
        <h2>Who We Are</h2>
        <p>Camping Adventures is your trusted partner in outdoor exploration, specializing in renting desert-specific camps, caravans, and equipment.</p>
        
        <h2>Our Mission</h2>
        <p>Our mission is to make outdoor adventures accessible for everyone by providing an easy-to-use platform that connects you with high-quality rental options. We aim to enhance your camping experience while promoting sustainable practices.</p>

        <h2>What We Offer</h2>
        <p>Our platform offers:</p>
        <ul>
            <li><strong>Real-Time Availability:</strong> Check the availability of camps, caravans, and equipment instantly.</li>
            <li><strong>Streamlined Booking:</strong> Enjoy a hassle-free booking process with clear pricing and options for delivery or self-pickup.</li>
            <li><strong>Feedback System:</strong> Share your experiences and help us improve our services.</li>
            <li><strong>Loyalty Programs:</strong> Benefit from discounts and special offers for our frequent customers.</li>
        </ul>

        <h2>Our Values</h2>
        <p>At Camping Adventures, we value sustainability, customer satisfaction, and community engagement. We strive to provide eco-friendly rental options that reduce waste while enabling you to enjoy the great outdoors.</p>

        <h2>Join Us</h2>
        <p>Whether you are a seasoned adventurer or planning your first camping trip, Camping Adventures is here to support you. Explore the beauty of nature with ease and comfort.</p>
    </section>
</main>

<footer>
    <p>&copy; 2025 Camping Adventures. All Rights Reserved.</p>
</footer>
</body>
</html>