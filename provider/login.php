<?php
session_start(); // Start the session

// Include the database connection
require_once('../php/db_config.php');  // Correct the path to db_config.php

// Check if the form is submitted
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the database to check if the provider exists
    $query = "SELECT * FROM provider WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $provider = $result->fetch_assoc();

    // Check if the provider exists and the password matches (plain-text password)
    if ($provider && $password == $provider['Password']) { // Password in plain text
        $_SESSION['provider_id'] = $provider['ProviderID'];
        $_SESSION['email'] = $provider['Email'];
        header("Location: services.php"); // Redirect to services.php
        exit();
    } else {
        $error_message = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provider Login</title>
    <link rel="stylesheet" href="stylee.css">
</head>
<body>
    <div class="login-container">
        <h2>Provider Login</h2>
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?= $error_message ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
