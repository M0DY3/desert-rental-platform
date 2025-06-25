<?php
session_start();
include 'php/db_config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['user'];

// Get UserID
$sql = "SELECT UserID FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    die("Error: User not found. <a href='login.php'>Login again</a>");
}

// Fetch reviews for the user
$review_sql = "SELECT f.FeedbackID, f.Rating, f.Comment, f.CreatedAt, i.Name AS ItemName, i.Price, i.ItemID, f.OrderID
               FROM feedback f 
               JOIN item i ON f.ItemID = i.ItemID
               WHERE f.UserID = ? 
               ORDER BY f.CreatedAt DESC";
$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $user_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reviews</title>
    <link rel="stylesheet" href="css/reviews.css">
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

        .reviews-list {
            margin-top: 20px;
        }

        .review-item {
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fafafa;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .review-item h3 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .review-item p {
            margin: 5px 0;
        }

        .review-item .rating {
            font-size: 18px;
            color: #f39c12;
        }

        .review-item .comment {
            margin-top: 10px;
        }

        .review-item .created-at {
            font-size: 12px;
            color: #888;
        }

        .review-item .view-order-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .review-item .view-order-link:hover {
            text-decoration: underline;
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
    <h1>My Reviews</h1>

    <?php if ($review_result->num_rows > 0): ?>
        <div class="reviews-list">
            <?php while ($review = $review_result->fetch_assoc()): ?>
                <div class="review-item">
                    <h3>
                        Review for: 
                        <a href="order_details.php?order_id=<?= htmlspecialchars($review['OrderID']) ?>&item_id=<?= htmlspecialchars($review['ItemID']) ?>" class="view-order-link">
                            <?= htmlspecialchars($review['ItemName']) ?> (Order #<?= htmlspecialchars($review['OrderID']) ?>)
                        </a>
                    </h3>
                    <div class="rating">
                        <strong>Rating:</strong> <?= str_repeat("★", (int)$review['Rating']) ?>
                    </div>
                    <div class="comment">
                        <strong>Comment:</strong> <?= htmlspecialchars($review['Comment']) ?>
                    </div>
                    <p class="created-at">Reviewed on: <?= htmlspecialchars($review['CreatedAt']) ?></p>
                    <p><strong>Price:</strong> $<?= htmlspecialchars($review['Price']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center;">You have not submitted any reviews yet.</p>
    <?php endif; ?>

    <a href="account.php" class="back-button">Back to Profile</a>
</div>

<footer>
    <p>&copy; 2025 Rental Services. All Rights Reserved.</p>
</footer>

</body>
</html>

<?php
$review_stmt->close();
$conn->close();
?>
