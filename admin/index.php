<?php
session_start(); // Start the session

include 'php/db_config.php'; // Ensure this path is correct

// Redirect if already logged in as admin
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$error = ""; // Variable for login error message

// Handle login for admin
if (isset($_POST['login'])) {
    // Sanitize and trim input values
    $email = trim($_POST['email']);
    $password = trim($_POST['pass']);

    // Validate email format
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Prepare and execute SQL query to check for the admin user
        $sql = "SELECT * FROM admin WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Direct comparison of the password (plain text)
            if ($password == $admin['Password']) {
                // Password is correct, start the session and store AdminID
                $_SESSION['logged_in'] = true;
                $_SESSION['user'] = $admin['Email'];
                $_SESSION['admin_id'] = $admin['AdminID'];  // Store AdminID in session
                
                // Redirect to the admin management page
                header("Location: manage.php");
                exit();
            } else {
                // Password mismatch
                $error = "Invalid password.";
            }
        } else {
            // Email doesn't exist in the database
            $error = "Email does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="wrapper">
        <h1>Admin Login</h1>
        <form method="POST">
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <div>
                <label for="email">Email: </label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label for="pass">Password: </label>
                <input type="password" name="pass" required>
            </div>
            <div>
                <input type="submit" name="login" value="Login">
            </div>
        </form>
    </div>
</body>
</html>
