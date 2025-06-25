<?php
session_start();
if (!isset($_SESSION['provider_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

require_once('../php/db_config.php'); // Correct path to db_config.php

// Provider's ID
$provider_id = $_SESSION['provider_id'];

// Get today's date for filtering
$today = date('Y-m-d');

// Check if a search query is provided
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Query to fetch user bookings for the provider's items (including the booking count for each item)
$query = "
    SELECT b.BookingID, u.Name AS UserName, u.Phone AS UserPhone, b.StartDate, b.EndDate, i.Name AS ItemName, 
           b.Status, b.TotalPrice, b.CancellationPolicy, i.ItemID
    FROM booking b
    JOIN item i ON b.ItemID = i.ItemID
    JOIN users u ON b.UserID = u.UserID
    WHERE i.ProviderID = ? AND b.StartDate >= ? 
    AND (i.Name LIKE ? OR u.Name LIKE ?)
    ORDER BY b.StartDate ASC"; // Only get current/future bookings

$stmt = $conn->prepare($query);
$search_like = "%$search_query%";
$stmt->bind_param("ssss", $provider_id, $today, $search_like, $search_like);
$stmt->execute();
$result = $stmt->get_result();

// Fetch bookings
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Get total number of bookings for the provider's items
$total_bookings_query = "
    SELECT COUNT(*) AS total_bookings
    FROM booking b
    JOIN item i ON b.ItemID = i.ItemID
    WHERE i.ProviderID = ? AND b.StartDate >= ?";
$total_bookings_stmt = $conn->prepare($total_bookings_query);
$total_bookings_stmt->bind_param("ss", $provider_id, $today);
$total_bookings_stmt->execute();
$total_bookings_result = $total_bookings_stmt->get_result();
$total_bookings = $total_bookings_result->fetch_assoc()['total_bookings'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Bookings</title>
    <link rel="stylesheet" href="stylee.css">
    <style>
        /* Search Bar Styles */
        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 100%;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        /* Booking Card Styles */
        .booking-card {
            display: flex;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow: hidden;
        }

        .booking-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
        }

        .booking-details h3 {
            margin: 0;
            color: #333;
        }

        .booking-details p {
            margin: 5px 0;
            color: #555;
        }

        /* Back Button */
        .back-btn {
            display: inline-block;
            background-color: #2c271a;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-btn:hover {
            background-color: #f8b500;
        }

        /* Live Booking Green Text */
        .live {
            color: green;
            font-weight: bold;
        }

        /* Red Text for "Days Left" */
        .days-left {
            color: red;
            font-weight: bold;
        }

        /* Booking Counter */
        .booking-count {
            font-size: 16px;
            color: #555;
            margin-top: 10px;
        }

        /* Total Bookings Counter */
        .total-bookings {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        /* Clear the container */
        .bookings-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>User Bookings</h2>

        <!-- Back button to services -->
        <a href="services.php" class="back-btn">Back to Services</a>

        <!-- Total Bookings Count -->
        <div class="total-bookings">
            <p><strong>Total Bookings: </strong><?php echo $total_bookings; ?></p>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <form method="GET" action="user_bookings.php">
                <input type="text" name="search" placeholder="Search by item or user name..." value="<?php echo htmlspecialchars($search_query); ?>" />
            </form>
        </div>

        <?php if (count($bookings) > 0): ?>
            <div class="bookings-container">
                <?php foreach ($bookings as $booking): ?>
                    <?php 
                        // Calculate the time left until the booking starts
                        $start_date = new DateTime($booking['StartDate']);
                        $today = new DateTime();
                        $interval = $today->diff($start_date);
                        $days_left = $interval->format('%a'); // Days left

                        // Check if booking is live (start date is today or in the future)
                        $is_live = $start_date >= $today; 
                    ?>

                    <div class="booking-card">
                        <div class="booking-details">
                            <h3><?php echo htmlspecialchars($booking['ItemName']); ?></h3>
                            <p><strong>User:</strong> <?php echo htmlspecialchars($booking['UserName']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['UserPhone']); ?></p>

                            <div class="dates">
                                <p><strong>Booking Dates:</strong> <?php echo $booking['StartDate']; ?> to <?php echo $booking['EndDate']; ?></p>

                                <?php if ($days_left == 0): ?>
                                    <p class="days-left">Days Left: 0 days</p> <!-- Red for 0 days -->
                                    <p class="live">Live Booking</p> <!-- Green for live bookings -->
                                <?php else: ?>
                                    <p><strong>Days Left:</strong> <?php echo $days_left; ?> days</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No bookings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
