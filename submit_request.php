<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $purpose = htmlspecialchars($_POST['purpose']);
    $arrival = $_POST['arrival'];
    $departure = $_POST['departure'];

    // Calculation logic for total duration
    $time1 = strtotime($arrival);
    $time2 = strtotime($departure);
    
    if ($time2 <= $time1) {
        die("<script>alert('Error: Departure must be after arrival.'); window.history.back();</script>");
    }

    $duration_seconds = $time2 - $time1;
    $duration_hours = round($duration_seconds / 3600, 1); 

    $tid = "VST-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $phone, $purpose, $duration_hours, $arrival, $departure, $tid])) {
        header("Location: visitor_status.php?tid=" . $tid);
        exit();
    }
}
?>