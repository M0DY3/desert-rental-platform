<?php
session_start();
include 'php/db_config.php'; // Ensure this path is correct

$error = ""; // Variable for login error message

// Handle login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['pass'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $sql = "SELECT * FROM Users WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['user'] = $user['Email'];  // Store user email or ID
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
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
    <title>Camping Adventures - Login</title>
</head>
<body>
    <header class="header">
        <a href="#" class="logo">
            <img src="images/logo.png" alt="Camping Logo">
        </a>
        <nav class="nav-items">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <a href="bucket.php">My Bucket</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Signup</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="wrapper">
        <div class="title-text">
            <div class="title login">Login Form</div>
        </div>
        <div class="form-container">
            <form action="" method="POST" class="login">
                <!-- Display error message -->
                <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
                <div class="field">
                    <input type="text" placeholder="Email Address" name="email" required>
                </div>
                <div class="field">
                    <input type="password" placeholder="Password" name="pass" required>
                </div>
                <div class="pass-link">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
                <div class="field btn">
                    <input type="submit" name="login" value="Login">
                </div>
                <div class="signup-link">
                    Not a member? <a href="signup.php">Signup now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
