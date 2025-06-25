<?php
$servername = "localhost";
$username = "root";  // اسم المستخدم الافتراضي في XAMPP
$password = "";      // كلمة المرور الافتراضية في XAMPP (غالبًا فارغة)
$dbname = "desert_rental_platform";  // اسم قاعدة البيانات

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  // في حال فشل الاتصال
}
?>
