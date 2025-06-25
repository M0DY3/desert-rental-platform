<?php 
session_start();
include 'php/db_config.php'; // Ensure this path is correct

$error = "";
$password_error = ""; 
$email_error = "";
$phone_error = "";
$name_error = "";
$address_error = "";

// Handle signup (server-side validation will remain the same)
if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['pass'];
    $confirm_password = $_POST['confirm_pass'];

    // Validate name length
    if (strlen($name) > 30) {
        $name_error = "Name cannot exceed 30 characters.";
    }

    // Validate phone number
    if (!preg_match("/^\d{10}$/", $phone)) {
        $phone_error = "Phone number must be exactly 10 digits.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format.";
    }

    // Validate password strength (at least 8 characters)
    if (strlen($password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    }

    // Validate address (max 30 characters)
    if (strlen($address) > 30) {
        $address_error = "Address cannot exceed 30 characters.";
    }

    if (!$name_error && !$phone_error && !$email_error && !$password_error && !$address_error && !$error) {
        // Check if email or phone already exists
        $sql = "SELECT * FROM Users WHERE Email = ? OR Phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['Email'] === $email) {
                    $email_error = "<span class='special-text'>Email</span> is already registered!";
                }
                if ($row['Phone'] === $phone) {
                    $phone_error = "<span class='special-text'>Phone number</span> is already registered!";
                }
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $sql = "INSERT INTO Users (Name, Phone, Email, Address, Password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $phone, $email, $address, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php"); // Redirect to login page
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
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
    <title>Camping Adventures - Signup</title>
    <style>
        .special-text {
            font-weight: bold;
            color: blue; /* Change to any color */
        }
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
    <script>
        // Live validation for each field
        function validatePhone() {
            const phone = document.getElementById("phone").value;
            const phoneError = document.getElementById("phoneError");
            const regex = /^\d{10}$/;
            if (!regex.test(phone)) {
                phoneError.innerHTML = "Phone number must be exactly 10 digits.";
            } else {
                phoneError.innerHTML = "";
            }
        }

        function validateName() {
            const name = document.getElementById("name").value;
            const nameError = document.getElementById("nameError");
            if (name.length > 30) {
                nameError.innerHTML = "Name cannot exceed 30 characters.";
            } else {
                nameError.innerHTML = "";
            }
        }

        function validateAddress() {
            const address = document.getElementById("address").value;
            const addressError = document.getElementById("addressError");
            if (address.length > 30) {
                addressError.innerHTML = "Address cannot exceed 30 characters.";
            } else {
                addressError.innerHTML = "";
            }
        }

        function validatePassword() {
            const password = document.getElementById("password").value;
            const passwordError = document.getElementById("passwordError");
            if (password.length > 30) {
                passwordError.innerHTML = "Password cannot exceed 30 characters.";
            } else {
                passwordError.innerHTML = "";
            }
        }

        function validateConfirmPassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const confirmPasswordError = document.getElementById("confirmPasswordError");
            if (password !== confirmPassword) {
                confirmPasswordError.innerHTML = "Passwords do not match.";
            } else {
                confirmPasswordError.innerHTML = "";
            }
        }

        // Event listeners for live validation
        document.getElementById("phone").addEventListener("input", validatePhone);
        document.getElementById("name").addEventListener("input", validateName);
        document.getElementById("address").addEventListener("input", validateAddress);
        document.getElementById("password").addEventListener("input", validatePassword);
        document.getElementById("confirm_password").addEventListener("input", validateConfirmPassword);
    </script>
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
            <div class="title signup">Signup Form</div>
        </div>
        <div class="form-container">
            <form action="" method="POST" class="signup">
                <div class="field">
                    <span id="nameError" class="error"><?php echo $name_error; ?></span>
                    <input type="text" placeholder="Name" name="name" id="name" required>
                </div>
                <div class="field">
                    <span id="phoneError" class="error"><?php echo $phone_error; ?></span>
                    <input type="text" placeholder="Phone" name="phone" id="phone" required>
                </div>
                <div class="field">
                    <span id="emailError" class="error"><?php echo $email_error; ?></span>
                    <input type="text" placeholder="Email Address" name="email" required>
                </div>
                <div class="field">
                    <span id="addressError" class="error"><?php echo $address_error; ?></span>
                    <select name="address" id="address" required>
                        <option value="" disabled selected>Select your region in KSA</option>
                        <option value="Tabuk Province">Tabuk</option>
                        <option value="Riyadh Province">Riyadh</option>
                        <option value="Northern Borders Province">Northern Borders</option>
                        <option value="Najran Province">Najran</option>
                        <option value="Medina Province">Medina</option>
                        <option value="Mecca Province">Mecca</option>
                        <option value="Jazan Province">Jazan</option>
                        <option value="Ha'il Province">Ha'il</option>
                        <option value="Eastern Province">Eastern Province</option>
                        <option value="Asir Province">Asir</option>
                        <option value="Al-Qassim Province">Al-Qassim</option>
                        <option value="Al-Jouf Province">Al-Jouf</option>
                        <option value="Al-Bahah Province">Al-Bahah</option>
                    </select>
                </div>
                <div class="field">
                    <span id="passwordError" class="error"><?php echo $password_error; ?></span>
                    <input type="password" placeholder="Password" name="pass" id="password" required>
                </div>
                <div class="field">
                    <span id="confirmPasswordError" class="error"><?php echo $password_error; ?></span>
                    <input type="password" placeholder="Confirm Password" name="confirm_pass" id="confirm_password" required>
                </div>
                <div class="field btn">
                    <input type="submit" name="signup" value="Signup">
                </div>
                <div class="signup-link">
                    Already a member? <a href="login.php">Login now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
