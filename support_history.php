<?php
session_start();
include 'php/db_config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['user'];

// Get user ID
$stmt = $conn->prepare("SELECT UserID FROM users WHERE Email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Fetch all support messages including admin responses
$stmt = $conn->prepare("SELECT id, message, submitted_at, response, responded_at FROM support_messages WHERE user_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$newResponseAvailable = false;
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
    if ($row['response']) {
        $newResponseAvailable = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Support Messages</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .support-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .support-msg {
            border-left: 5px solid #007bff;
            background: #f9f9f9;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .support-msg h4 {
            margin: 0 0 5px;
            color: #007bff;
        }
        .support-msg p {
            margin: 5px 0;
        }
        .response-box {
            margin-top: 10px;
            background: #e6ffe6;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .no-msg {
            text-align: center;
            padding: 40px;
            color: gray;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }

        /* Notification popup */
        .notify {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
            z-index: 9999;
        }
        .notify button {
            background: #fff;
            color: #007bff;
            border: none;
            padding: 5px 10px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .notify button:hover {
            background: #e6e6e6;
        }
    </style>
</head>
<body>

<header>
    <img src="images/logo.png" alt="Logo">
    <h1>Your Support Messages</h1>
</header>

<main>
    <div class="support-container">
        <?php if (count($messages) > 0): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="support-msg">
                    <h4>Submitted on <?php echo date("F j, Y - g:i A", strtotime($msg['submitted_at'])); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>

                    <?php if ($msg['response']): ?>
                        <div class="response-box">
                            <strong>Admin Response:</strong>
                            <p><?php echo nl2br(htmlspecialchars($msg['response'])); ?></p>
                            <small>Responded on <?php echo date("F j, Y - g:i A", strtotime($msg['responded_at'])); ?></small>
                        </div>
                    <?php else: ?>
                        <p><strong>Status:</strong> Awaiting admin response</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-msg">
                <p>You haven't sent any support messages yet.</p>
            </div>
        <?php endif; ?>
        <a class="back-link" href="account.php">← Back to Account</a>
    </div>
</main>

<div class="notify" id="notify">
    <span>You have a new response from support!</span>
    <button onclick="readNotice()">Read</button>
</div>

<footer>
    <p>&copy; 2025 Camping Adventures. All Rights Reserved.</p>
</footer>

<script>
// Check if new response and user hasn't read it yet
<?php if ($newResponseAvailable): ?>
    if (!localStorage.getItem('support_notice_read')) {
        document.getElementById('notify').style.display = 'block';
    }
<?php endif; ?>

function readNotice() {
    localStorage.setItem('support_notice_read', 'true');
    document.getElementById('notify').style.display = 'none';
}
</script>

</body>
</html>
