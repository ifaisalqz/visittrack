<?php
include 'includes/db.php';
// هذا الكود سيقوم بتشفير كلمة admin123 وحفظها في قاعدة البيانات
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hash' WHERE username = 'admin'");
echo "تم تحديث وتشفير الباسورد بنجاح! يمكنك الآن تسجيل الدخول.";
?>