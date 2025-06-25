<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'php/db_config.php';

$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

// Fetch item
$stmt = $conn->prepare("SELECT ItemID, Name, Description, Price FROM item WHERE ItemID = ?");
$stmt->bind_param('i', $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) die("Item not found");

// Fetch images
$images = [];
$stmt = $conn->prepare("SELECT ImageURL FROM img WHERE ItemID = ?");
$stmt->bind_param('i', $item_id);
$stmt->execute();
$res = $stmt->get_result();
while ($img = $res->fetch_assoc()) $images[] = $img['ImageURL'];

$message = "";
$final_price = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = new DateTime($_POST['start_date']);
    $end = new DateTime($_POST['end_date']);
    $method = $_POST['payment_method'];
    $coupon = trim($_POST['coupon_code'] ?? '');
    $days = $start->diff($end)->days;

    if ($days <= 0) {
        $message = "Invalid date range.";
    } else {
        // Check if item is already booked for the selected dates
        $stmt = $conn->prepare("SELECT * FROM booking WHERE ItemID = ? AND ((StartDate BETWEEN ? AND ?) OR (EndDate BETWEEN ? AND ?))");
        $stmt->bind_param('issss', $item_id, $_POST['start_date'], $_POST['end_date'], $_POST['start_date'], $_POST['end_date']);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $message = "❌ This item is already booked for the selected dates. Please choose a different date range.";
        } else {
            $price = $item['Price'] * $days;
            $discount = 0;

            if ($coupon) {
                $stmt = $conn->prepare("SELECT DiscountPercentage FROM promotions WHERE Code = ?");
                $stmt->bind_param('s', $coupon);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $discount = (float)$row['DiscountPercentage'];
                    $price -= $price * ($discount / 100);
                }
            }

            $is_paid = in_array($method, ['credit-card', 'paypal', 'bank-transfer']);
            if ($is_paid) {
                $final_price = round($price, 2);
                $status = "Confirmed";
                $booking_date = date('Y-m-d H:i:s');
                $cancellation = "No Refund";
                $user_id = $_SESSION['user_id'] ?? 1;

                $stmt = $conn->prepare("INSERT INTO booking 
                    (UserID, BookingDate, StartDate, EndDate, Status, TotalPrice, CancellationPolicy, ItemID)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    'issssssi',
                    $user_id,
                    $booking_date,
                    $_POST['start_date'],
                    $_POST['end_date'],
                    $status,
                    $final_price,
                    $cancellation,
                    $item_id
                );

                if ($stmt->execute()) {
                    $points_earned = floor($final_price / 2);
                    $check = $conn->prepare("SELECT ProgramID FROM loyaltyprogram WHERE UserID = ?");
                    $check->bind_param('i', $user_id);
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows > 0) {
                        $update = $conn->prepare("UPDATE loyaltyprogram SET Points = Points + ?, LastUpdated = NOW() WHERE UserID = ?");
                        $update->bind_param('ii', $points_earned, $user_id);
                        $update->execute();
                    } else {
                        $insert = $conn->prepare("INSERT INTO loyaltyprogram (UserID, Points, LastUpdated) VALUES (?, ?, NOW())");
                        $insert->bind_param('ii', $user_id, $points_earned);
                        $insert->execute();
                    }

                    $message = "✅ Booking successful.<br><strong>Total: $$final_price</strong><br>Status: <strong>$status</strong><br>🎁 You earned <strong>$points_earned points</strong>.";
                } else {
                    $message = "❌ Booking failed: " . $stmt->error;
                }
            } else {
                $message = "❌ Payment failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Book: <?= htmlspecialchars($item['Name']) ?></title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f6fa;
      color: #333;
      margin: 0;
    }
    .container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 0 12px rgba(0,0,0,0.05);
    }
    .gallery {
      position: relative;
      margin-bottom: 20px;
    }
    .gallery img {
      width: 100%;
      max-height: 400px;
      object-fit: cover;
      border-radius: 10px;
    }
    .arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 2rem;
      background: #333;
      color: #fff;
      border: none;
      padding: 8px 12px;
      border-radius: 50%;
      cursor: pointer;
    }
    .arrow.left { left: 10px; }
    .arrow.right { right: 10px; }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input, select {
      width: 100%;
      padding: 10px;
      font-size: 1rem;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    .payment-fields > div { display: none; }

    .price-box {
      margin-top: 20px;
      background: #f0f0f0;
      padding: 12px;
      border-radius: 8px;
      font-size: 1.1rem;
    }

    .btn {
      background: #28a745;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      margin-top: 20px;
      cursor: pointer;
    }

    .btn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .msg {
      margin-top: 20px;
      padding: 12px;
      background: #e7f7ff;
      border-left: 4px solid #1890ff;
    }
  </style>
</head>
<body>
<div class="container">
  <h2><?= htmlspecialchars($item['Name']) ?></h2>
  <p><?= htmlspecialchars($item['Description']) ?></p>
  <p><strong>Price:</strong> $<span id="pricePerNight"><?= $item['Price'] ?></span> per night</p>

  <div class="gallery">
    <button class="arrow left" onclick="changeImage(-1)">&#10094;</button>
    <img id="mainImage" src="<?= htmlspecialchars($images[0] ?? 'default.jpg') ?>" alt="Item image">
    <button class="arrow right" onclick="changeImage(1)">&#10095;</button>
  </div>

  <form method="POST">
    <label>Start Date:
      <input type="date" name="start_date" id="startDate" required>
    </label>

    <label>End Date:
      <input type="date" name="end_date" id="endDate" required>
    </label>

    <label>Coupon Code (optional):
      <input type="text" name="coupon_code" id="couponCode">
    </label>

    <label>Payment Method:
      <select name="payment_method" id="paymentMethod" required>
        <option value="">-- Select --</option>
        <option value="credit-card">Credit Card</option>
        <option value="paypal">PayPal</option>
        <option value="bank-transfer">Bank Transfer</option>
      </select>
    </label>

    <div class="payment-fields">
      <div id="credit-card">
        <label>Name on Card: <input type="text"></label>
        <label>Card Number: <input type="text"></label>
        <label>Expiry Date: <input type="text"></label>
        <label>CVV: <input type="text"></label>
      </div>
      <div id="paypal">
        <label>PayPal Email: <input type="email"></label>
      </div>
      <div id="bank-transfer">
        <label>Bank Account #: <input type="text"></label>
      </div>
    </div>

    <div class="price-box">
      Total Price: $<span id="totalPrice">0.00</span>
    </div>

    <button type="submit" class="btn" id="submitBtn" disabled>Confirm Booking</button>
  </form>

  <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
  <?php endif; ?>
</div>

<script>
const images = <?= json_encode($images) ?>;
let imgIndex = 0;
function changeImage(dir) {
  imgIndex = (imgIndex + dir + images.length) % images.length;
  document.getElementById('mainImage').src = images[imgIndex];
}

const price = parseFloat(document.getElementById('pricePerNight').textContent);
const start = document.getElementById('startDate');
const end = document.getElementById('endDate');
const coupon = document.getElementById('couponCode');
const total = document.getElementById('totalPrice');
const method = document.getElementById('paymentMethod');
const submit = document.getElementById('submitBtn');

function getDays(s, e) {
  const sd = new Date(s), ed = new Date(e);
  return (ed - sd > 0) ? (ed - sd) / (1000 * 60 * 60 * 24) : 0;
}

function updateTotal() {
  const s = start.value, e = end.value;
  const days = getDays(s, e);
  if (days <= 0) {
    total.textContent = '0.00';
    submit.disabled = true;
    return;
  }

  let base = days * price;
  const code = coupon.value.trim();

  if (code) {
    fetch(`check_coupon.php?code=${encodeURIComponent(code)}`)
      .then(r => r.json())
      .then(d => {
        const discount = d.status === 'success' ? parseFloat(d.discount) : 0;
        const final = base - base * (discount / 100);
        total.textContent = final.toFixed(2);
        submit.disabled = false;
      });
  } else {
    total.textContent = base.toFixed(2);
    submit.disabled = false;
  }
}

[start, end, coupon].forEach(el => el.addEventListener('input', updateTotal));

method.addEventListener('change', e => {
  document.querySelectorAll('.payment-fields > div').forEach(d => d.style.display = 'none');
  if (e.target.value) document.getElementById(e.target.value).style.display = 'block';
});
</script>
</body>
</html>

<?php $conn->close(); ?>
