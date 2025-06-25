<?php
session_start();
include 'php/db_config.php'; // Adjust this path if necessary

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ensure that order_id and item_id are present in the URL
if (!isset($_GET['order_id']) || !isset($_GET['item_id'])) {
    die("Error: Missing order or item ID.");
}

// Retrieve order_id and item_id from the URL
$order_id = $_GET['order_id'];
$item_id = $_GET['item_id'];

// Retrieve user email from session
$user_email = $_SESSION['user'];

// Fetch UserID from the database
$sql = "SELECT UserID FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Ensure we have a valid UserID
if (!$user_id) {
    die("Error: User not found. <a href='login.php'>Login again</a>");
}

// Fetch order details for the logged-in user
$order_sql = "SELECT * FROM orders WHERE OrderID = ? AND UserID = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    // Debugging message added
    echo "Order not found or you do not have access to this order. <br>";
    echo "OrderID: $order_id, UserID: $user_id"; // Displaying order and user ID for debugging
    die("Error: Order not found or you do not have access to this order.");
}

$order = $order_result->fetch_assoc();
$order_stmt->close();

// Fetch item details for the given item_id in the order
$item_sql = "SELECT * FROM item WHERE ItemID = ?";
$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $item_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

if ($item_result->num_rows === 0) {
    die("Error: Item not found.");
}

$item = $item_result->fetch_assoc();
$item_stmt->close();

// Fetch feedback details for the item
$feedback_sql = "SELECT * FROM feedback WHERE UserID = ? AND ItemID = ?";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("ii", $user_id, $item_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();
$feedback = $feedback_result->fetch_assoc();
$feedback_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="css/orders.css">
    <style>
        body {
            background-color: #f4f4f9;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        .order-details,
        .item-details,
        .feedback-section {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fafafa;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .order-details p,
        .item-details p {
            margin: 5px 0;
        }

        .item-details img {
            width: 150px;
            height: auto;
            margin-right: 20px;
        }

        .feedback-section {
            margin-top: 20px;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #f1f1f1;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Order Details for Order #<?php echo htmlspecialchars($order['OrderID']); ?></h1>

    <!-- Order Details Section -->
    <div class="order-details">
        <h2>Order Information</h2>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['OrderDate']); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo htmlspecialchars($order['TotalAmount']); ?></p>
        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['PaymentStatus']); ?></p>
        <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['DeliveryAddress']); ?></p>
        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['OrderStatus']); ?></p>
    </div>

    <!-- Item Details Section -->
    <div class="item-details">
        <h2>Item Information</h2>
        <p><strong>Item Name:</strong> <?php echo htmlspecialchars($item['Name']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($item['Description']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($item['Price']); ?></p>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($item['Type']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($item['City']); ?></p>
        <p><strong>Maps URL:</strong> <a href="<?php echo htmlspecialchars($item['MapsURL']); ?>" target="_blank">View on Map</a></p>

        <!-- Item Images -->
        <h3>Item Images</h3>
        <?php
        $img_sql = "SELECT ImageURL FROM img WHERE ItemID = ?";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("i", $item['ItemID']);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();
        if ($img_result->num_rows > 0):
            while ($img = $img_result->fetch_assoc()):
                $image_path = htmlspecialchars($img['ImageURL']);
                ?>
                <img src="<?php echo $image_path; ?>" alt="Item Image">
            <?php
            endwhile;
        else:
            echo "<p>No images available.</p>";
        endif;
        $img_stmt->close();
        ?>
    </div>

    <!-- Feedback Section -->
    <div class="feedback-section">
        <h2>Feedback</h2>
        <?php if ($feedback): ?>
            <p><strong>Rating:</strong> <?php echo htmlspecialchars($feedback['Rating']); ?> Stars</p>
            <p><strong>Comment:</strong> <?php echo htmlspecialchars($feedback['Comment']); ?></p>
            <p><em>Submitted on: <?php echo htmlspecialchars($feedback['CreatedAt']); ?></em></p>
        <?php else: ?>
            <p>You have not provided feedback for this item yet.</p>
        <?php endif; ?>
    </div>

    <a href="orders.php" class="back-button">Back to My Orders</a>
</div>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>

<?php
// Close database connections
$conn->close();
?>
