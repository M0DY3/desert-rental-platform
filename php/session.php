<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Retrieve session values
$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];
$user_phone = $_SESSION['user_phone'];
$user_address = $_SESSION['user_address'];
?>
