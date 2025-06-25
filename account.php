<?php 
session_start();
include 'php/db_config.php'; // Ensure this file exists and is correctly set up

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Retrieve user email from session
$user_email = $_SESSION['user']; 

// Check if the logged-in user is in the 'admin' table, if so, redirect to manage.php
$sql_admin_check = "SELECT * FROM admin WHERE Email = ?";
$stmt_admin_check = $conn->prepare($sql_admin_check);
$stmt_admin_check->bind_param("s", $user_email);
$stmt_admin_check->execute();
$admin_result = $stmt_admin_check->get_result();

// If the user is an admin, redirect them to manage.php
if ($admin_result->num_rows > 0) {
    header("Location: http://localhost/website/admin/manage.php");
    exit;
}

// Fetch user details from the database
$sql = "SELECT * FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['Name'];
    $phone = $user['Phone'];
    $address = $user['Address'];
    $email = $user['Email'];
} else {
    echo "User not found.";
    exit;
}

// Handle profile update
if (isset($_POST['save_changes'])) {
    $updated_name = $_POST['name'];
    $updated_phone = $_POST['phone'];
    $updated_address = $_POST['address'];

    // Check if the new phone number already exists
    $check_phone_sql = "SELECT * FROM Users WHERE Phone = ? AND Email != ?";
    $check_phone_stmt = $conn->prepare($check_phone_sql);
    $check_phone_stmt->bind_param("ss", $updated_phone, $user_email);
    $check_phone_stmt->execute();
    $phone_result = $check_phone_stmt->get_result();

    if ($phone_result->num_rows > 0) {
        echo "<script>alert('This phone number is already registered. Please use a different number.');</script>";
    } else {
        // If phone number is not registered, update the user's information
        $update_sql = "UPDATE Users SET Name = ?, Phone = ?, Address = ? WHERE Email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssss", $updated_name, $updated_phone, $updated_address, $user_email);
        
        if ($update_stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='account.php';</script>";
        } else {
            echo "Failed to update profile.";
        }
    }
}

// Handle password update
if (isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE Users SET Password = ? WHERE Email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $user_email);

        if ($update_stmt->execute()) {
            echo "<script>alert('Password updated successfully!'); window.location.href='account.php';</script>";
        } else {
            echo "Failed to update password.";
        }
    }
}

// Fetch loyalty program points
$loyalty_sql = "SELECT * FROM loyaltyprogram WHERE UserID = (SELECT UserID FROM Users WHERE Email = ?)";
$loyalty_stmt = $conn->prepare($loyalty_sql);
$loyalty_stmt->bind_param("s", $user_email);
$loyalty_stmt->execute();
$loyalty_result = $loyalty_stmt->get_result();

// Check if loyalty program exists for the user
if ($loyalty_result->num_rows > 0) {
    $loyalty = $loyalty_result->fetch_assoc();
    $points = $loyalty['Points'];
} else {
    $points = 0; // If no loyalty program data exists for the user
}

// Check if in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] == 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/account.css">
    <style>
        body {
            background-color: #f7f8fa;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            background-color: #007bff;
            padding: 20px;
            color: white;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .logo img {
            max-width: 150px;
        }

        .header .back-button button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .form-group input:disabled {
            background-color: #f1f1f1;
        }

        .continue-button, .order-button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .continue-button:hover, .order-button:hover {
            background-color: #0056b3;
        }

        .orders-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .orders-buttons a {
            background-color: #17a2b8;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            width: 30%;
            text-transform: uppercase;
        }

        .orders-buttons a:hover {
            background-color: #117a8b;
        }

        .icon {
            width: 20px;
            margin-left: 10px;
        }

        footer {
            background-color: #f1f1f1;
            padding: 10px;
            text-align: center;
            font-size: 14px;
            margin-top: 40px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }

            .orders-buttons {
                flex-direction: column;
            }

            .orders-buttons a {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        <a href="index.php"><img src="images/logo.png" alt="Logo"></a>
    </div>
    <div class="back-button">
        <a href="index.php"><button>Back to Home</button></a>
        <a href="php/logout.php"><button>Logout</button></a>
    </div>
</div>

<div class="container">
    <!-- Loyalty Program Points Displayed at the Top -->
    <div class="form-group">
        <h3>Loyalty Program</h3>
        <p>Your current points: <strong><?php echo $points; ?></strong></p>
    </div>

    <h1>Profile</h1>
    <div class="profile-pic">
        <p>Welcome back, <?php echo htmlspecialchars($name); ?>!</p>
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>" <?php echo $edit_mode ? '' : 'disabled'; ?>>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone); ?>" <?php echo $edit_mode ? '' : 'disabled'; ?> id="phone-input" pattern="[0-9]{10}" maxlength="10">
        </div>
        <div class="form-group">
            <label for="address">Address (Select Region)</label>
            <select name="address" id="address" required <?php echo $edit_mode ? '' : 'disabled'; ?>>
                <option value="" disabled selected>Select your region in KSA</option>
                <option value="Tabuk Province" <?php echo $address == 'Tabuk Province' ? 'selected' : ''; ?>>Tabuk</option>
                <option value="Riyadh Province" <?php echo $address == 'Riyadh Province' ? 'selected' : ''; ?>>Riyadh</option>
                <option value="Northern Borders Province" <?php echo $address == 'Northern Borders Province' ? 'selected' : ''; ?>>Northern Borders</option>
                <option value="Najran Province" <?php echo $address == 'Najran Province' ? 'selected' : ''; ?>>Najran</option>
                <option value="Medina Province" <?php echo $address == 'Medina Province' ? 'selected' : ''; ?>>Medina</option>
                <option value="Mecca Province" <?php echo $address == 'Mecca Province' ? 'selected' : ''; ?>>Mecca</option>
                <option value="Jazan Province" <?php echo $address == 'Jazan Province' ? 'selected' : ''; ?>>Jazan</option>
                <option value="Ha'il Province" <?php echo $address == 'Ha\'il Province' ? 'selected' : ''; ?>>Ha'il</option>
                <option value="Eastern Province" <?php echo $address == 'Eastern Province' ? 'selected' : ''; ?>>Eastern Province</option>
                <option value="Asir Province" <?php echo $address == 'Asir Province' ? 'selected' : ''; ?>>Asir</option>
                <option value="Al-Qassim Province" <?php echo $address == 'Al-Qassim Province' ? 'selected' : ''; ?>>Al-Qassim</option>
                <option value="Al-Jouf Province" <?php echo $address == 'Al-Jouf Province' ? 'selected' : ''; ?>>Al-Jouf</option>
                <option value="Al-Bahah Province" <?php echo $address == 'Al-Bahah Province' ? 'selected' : ''; ?>>Al-Bahah</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        </div>

        <div class="form-group">
            <button type="submit" name="update_password" class="continue-button">Update Password</button>
        </div>
        
        <div class="form-group">
            <?php if (!$edit_mode): ?>
                <a href="?edit=true"><button type="button" class="continue-button">Edit Profile</button></a>
            <?php else: ?>
                <a href="?edit=false"><button type="button" class="continue-button">Cancel Edit</button></a>
                <button type="submit" name="save_changes" class="continue-button">Save Changes</button>
            <?php endif; ?>
        </div>
    </form>

    <!-- Orders, Shipped & Reviews Section -->
    <div class="form-group">
        <label for="orders">My Orders</label>
        <div class="orders-buttons">
            <a href="orders.php" class="order-button">Orders</a>
            <img src="images/Orders.png" alt="Orders Icon" class="icon">
            
          
            
            <a href="review.php" class="order-button">Review</a>
            <img src="images/review.png" alt="Review Icon" class="icon">
            
            <a href="support_history.php" class="order-button">View My Support Messages</a>

        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>
