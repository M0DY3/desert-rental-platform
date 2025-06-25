<?php
session_start();
$feedback = '';
include('php/db_config.php');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT email, name FROM users WHERE UserID = ?");
    if (!$stmt) die("Query error: " . $conn->error);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userEmail, $userName);
    $stmt->fetch();
    $stmt->close();

    // Check last message timestamp
    $stmt = $conn->prepare("SELECT submitted_at FROM support_messages WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    $canSend = true;

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($lastSubmittedAt);
        $stmt->fetch();

        $lastDate = new DateTime($lastSubmittedAt);
        $now = new DateTime();
        $diff = $lastDate->diff($now);

        if ($diff->days < 10) {
            $canSend = false;
            $feedback = "You can send a new support message after 10 days.";
        }
    }
    $stmt->close();

    if ($_SERVER["REQUEST_METHOD"] === "POST" && $canSend) {
        $message = trim($_POST['message']);
        if (strlen($message) > 500) {
            $feedback = "Message should be under 500 characters.";
        } else {
            $stmt = $conn->prepare("INSERT INTO support_messages (user_id, email, message) VALUES (?, ?, ?)");
            if (!$stmt) die("Insert error: " . $conn->error);
            $stmt->bind_param("iss", $userId, $userEmail, $message);
            if ($stmt->execute()) {
                $feedback = "Thank you for your message. We'll get back to you shortly.";
            } else {
                $feedback = "There was an error saving your message.";
            }
            $stmt->close();
        }
    }
} else {
    $feedback = "You must be logged in to contact us.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Camping Adventures</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        main {
            padding: 40px;
            background-color: #f4f4f4;
        }

        .contact {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .contact h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }

        .contact p {
            font-size: 1em;
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            color: #333;
            background-color: #f9f9f9;
            resize: vertical;
        }

        .form-group textarea:focus {
            border-color: #006400;
            background-color: #ffffff;
        }

        button {
            padding: 15px 25px;
            background-color: #006400;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #004d00;
        }

        .feedback {
            margin-top: 20px;
            font-size: 1.1em;
            color: #006400;
            text-align: center;
        }

        .feedback.error {
            color: red;
        }

        .feedback.success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <img src="images/logo.png" alt="logo">
    <h1>Contact Us</h1>
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

<main>
    <section class="contact">
        <h2>Get in Touch</h2>
        <p>
    If you have any questions or need help, send us a message below.<br>
    For urgent matters, contact us directly:<br><br>
    <a href="mailto:moh9238moh@gmail.com" style="color:#006400; font-weight:bold; text-decoration:none;">
        📧 moh9238moh@gmail.com
    </a><br>
    <a href="https://wa.me/966557883307" target="_blank" style="color:#006400; font-weight:bold; text-decoration:none;">
        📱 +966 557883307 (WhatsApp)
    </a>
</p>


        <?php if (!empty($feedback)): ?>
            <p class="feedback <?php echo (strpos($feedback, 'Thank') !== false) ? 'success' : 'error'; ?>">
                <?= $feedback ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $canSend): ?>
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label for="message">Your Message (Max 500 characters)</label>
                    <textarea name="message" id="message" maxlength="500" required></textarea>
                </div>

                <button type="submit">Send Message</button>
            </form>
        <?php elseif (!$canSend && isset($_SESSION['logged_in'])): ?>
            <p>You’ve already submitted a support message recently. Please wait 10 days to submit another.</p>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2025 Camping Adventures. All Rights Reserved.</p>
</footer>

</body>
</html>
