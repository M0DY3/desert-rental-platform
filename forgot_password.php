<?php
session_start();
include 'php/db_config.php'; // Ensure this path is correct

$message = ""; // Variable for messages
$error = ""; // Variable for error messages

// Handle password reset request
if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if the email exists in the database
        $sql = "SELECT * FROM Users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Create a unique token for the password reset
            $token = bin2hex(random_bytes(50));

            // Store token in the database (you might want to create a separate table for resets)
            $sql = "UPDATE Users SET reset_token = ? WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();

            // Send email with reset link (implement your own email function)
            $reset_link = "http://yourwebsite.com/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $body = "Click this link to reset your password: " . $reset_link;
            // mail($email, $subject, $body); // Uncomment to send the email

            $message = "A password reset link has been sent to your email address.";
        } else {
            $error = "Email does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/logo.png" type="image/icon type">
    <link rel="stylesheet" href="css/login.css">
    <title>Camping Adventures - Forgot Password</title>
</head>
<body>
    <header class="header">
        <a href="#" class="logo">
            <img src="images/logo.png" alt="Camping Logo">
        </a>
        <nav class="nav-items">
            <a href="index.php">Home</a>
            <a href="signup.php">Signup</a>
            <a href="about.php">About Us</a>
        </nav>
    </header>
    <div class="wrapper">
        <div class="title-text">
            <div class="title">Forgot Password</div>
        </div>
        <div class="form-container">
            <form action="" method="POST" class="forgot-password">
                <!-- Display messages -->
                <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
                <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
                <div class="field">
                    <input type="text" placeholder="Email Address" name="email" required>
                </div>
                <div class="field btn">
                    <input type="submit" name="reset" value="Send Reset Link">
                </div>
                <div class="signup-link">
                    Remembered your password? <a href="login.php">Login now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>