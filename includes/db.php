<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "visit_track";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set timezone to Riyadh to ensure accurate time calculation
    date_default_timezone_set('Asia/Riyadh');
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>