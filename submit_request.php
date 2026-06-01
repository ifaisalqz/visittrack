<?php
require 'vendor/autoload.php';
include 'includes/db.php';
include 'includes/mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = htmlspecialchars($_POST['name']);
    $phone      = htmlspecialchars($_POST['phone']);
    $purpose    = htmlspecialchars($_POST['purpose']);
    $arrival    = $_POST['arrival'];
    $departure  = $_POST['departure'];
    $host_name  = htmlspecialchars($_POST['host_name']);
    $national_id = htmlspecialchars($_POST['national_id']);
    $email      = htmlspecialchars($_POST['email']);

    $car_model    = trim($_POST['car_model']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_details = '';
    if (!empty($car_model) || !empty($plate_number)) {
        $vehicle_details = htmlspecialchars($car_model . ' | ' . strtoupper($plate_number));
    }

    $time1 = strtotime($arrival);
    $time2 = strtotime($departure);
    if ($time2 <= $time1) {
        die("<script>alert('Error: Departure must be after arrival.'); window.history.back();</script>");
    }
    $duration_hours = round(($time2 - $time1) / 3600, 1);

    $tid = "VST-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, vehicle_details, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt->execute([$name, $phone, $host_name, $national_id, $email, $vehicle_details, $purpose, $duration_hours, $arrival, $departure, $tid])) {

        // إرسال إيميل التأكيد بالتصميم الموحد
        $emailData = buildEmailHtml('submitted', [
            'name'      => $name,
            'tid'       => $tid,
            'arrival'   => $arrival,
            'departure' => $departure,
        ]);
        @sendVisitEmail($email, $name, $emailData['subject'], $emailData['html']);

        header("Location: register.php?success=1&tid=" . $tid);
        exit();
    }
}
?>
