<?php
require 'vendor/autoload.php';
include 'includes/db.php';
include 'includes/mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = htmlspecialchars($_POST['name']);
    $phone       = htmlspecialchars($_POST['phone']);
    $purpose     = htmlspecialchars($_POST['purpose']);
    $arrival     = $_POST['arrival'];
    $departure   = $_POST['departure'];
    $visit_date  = $_POST['visit_date'] ?? date('Y-m-d');
    $host_name   = htmlspecialchars($_POST['host_name']);
    $national_id = htmlspecialchars($_POST['national_id']);
    $email       = htmlspecialchars($_POST['email']);

    // ── التحقق من يوم العمل (أحد=0 ... خميس=4، جمعة=5، سبت=6) ──
    $dayOfWeek = (int)date('N', strtotime($visit_date)); // 1=Mon...7=Sun
    $phpDay    = (int)date('w', strtotime($visit_date)); // 0=Sun,5=Fri,6=Sat
    if ($phpDay === 5 || $phpDay === 6) {
        die("<script>alert('يوم الجمعة والسبت عطلة — اختر من الأحد إلى الخميس'); window.history.back();</script>");
    }
    if ($visit_date < date('Y-m-d')) {
        die("<script>alert('تاريخ الزيارة مضى — اختر تاريخاً مستقبلياً'); window.history.back();</script>");
    }

    // ── التحقق من ساعات الدوام ──
    $toMins  = fn($t) => (int)explode(':',$t)[0]*60 + (int)explode(':',$t)[1];
    $OPEN    = 7*60;
    $CLOSE   = 15*60 + 30;

    // إذا اليوم ذا — تحقق من الوقت الحالي أيضاً
    if ($visit_date === date('Y-m-d')) {
        $nowMins = (int)date('H')*60 + (int)date('i');
        if ($nowMins > $CLOSE) {
            die("<script>alert('الشركة مغلقة اليوم — اختر يوم عمل قادم'); window.history.back();</script>");
        }
        if ($toMins($arrival) < $nowMins) {
            die("<script>alert('وقت الوصول مضى — اختر وقتاً مستقبلياً'); window.history.back();</script>");
        }
    }
    if ($toMins($arrival) < $OPEN || $toMins($arrival) > $CLOSE) {
        die("<script>alert('وقت الوصول خارج ساعات العمل (07:00 ص — 03:30 م)'); window.history.back();</script>");
    }
    if ($toMins($departure) < $OPEN || $toMins($departure) > $CLOSE) {
        die("<script>alert('وقت المغادرة خارج ساعات العمل (07:00 ص — 03:30 م)'); window.history.back();</script>");
    }

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

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, vehicle_details, purpose, duration_hours, arrival_time, departure_time, visit_date, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $phone, $host_name, $national_id, $email, $vehicle_details, $purpose, $duration, $arrival, $departure, $visit_date, $tid])) {

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
