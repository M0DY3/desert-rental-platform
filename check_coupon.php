<?php
include 'php/db_config.php';
header('Content-Type: application/json');

$code = $_GET['code'] ?? '';
if (!$code) {
    echo json_encode(['status' => 'error', 'message' => 'No code']);
    exit;
}

$stmt = $conn->prepare("SELECT DiscountPercentage FROM promotions WHERE Code = ?");
$stmt->bind_param('s', $code);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'discount' => $row['DiscountPercentage']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid']);
}
$conn->close();
