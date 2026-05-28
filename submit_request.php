<?php
// تضمين ملفات PHPMailer 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// تأكد من مسار الـ autoload إذا استخدمت Composer
require 'vendor/autoload.php'; 

include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استقبال البيانات الأساسية
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $purpose = htmlspecialchars($_POST['purpose']);
    $arrival = $_POST['arrival'];
    $departure = $_POST['departure'];
    
    // استقبال البيانات الجديدة
    $host_name = htmlspecialchars($_POST['host_name']);
    $national_id = htmlspecialchars($_POST['national_id']);
    $email = htmlspecialchars($_POST['email']);
    
    // تفاصيل المركبة
    $car_model = trim($_POST['car_model']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_details = "";
    if(!empty($car_model) || !empty($plate_number)) {
        $vehicle_details = htmlspecialchars($car_model . " | " . strtoupper($plate_number));
    }

    // حساب المدة الزمنية
    $time1 = strtotime($arrival);
    $time2 = strtotime($departure);
    if ($time2 <= $time1) {
        die("<script>alert('Error: Departure must be after arrival.'); window.history.back();</script>");
    }
    $duration_hours = round(($time2 - $time1) / 3600, 1); 

    // توليد رقم التتبع
    $tid = "VST-" . rand(1000, 9999);

    // إدخال البيانات في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, vehicle_details, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $phone, $host_name, $national_id, $email, $vehicle_details, $purpose, $duration_hours, $arrival, $departure, $tid])) {
        
        // ==========================================
        // بداية كود إرسال الإيميل (Microsoft 365)
        // ==========================================
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            
            // إعدادات خادم Microsoft 365
            $mail->Host       = 'smtp.office365.com'; 
            $mail->SMTPAuth   = true;
            
            // بيانات الدخول لحساب Microsoft 365
            $mail->Username   = 'noreply@faisal.biz'; 
            $mail->Password   = 'bhwvxcvzsqmvnsgj';    
            
            // التشفير والمنفذ المخصص لمايكروسوفت
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // إعدادات المرسل والمستقبل (يجب أن يكون المرسل هو نفسه حساب الـ Username)
            $mail->setFrom('noreply@faisal.biz', 'Visit Track System');
            $mail->addAddress($email, $name); 
            
            // محتوى الإيميل
            $mail->isHTML(true);
            $mail->Subject = 'Your Visitor Pass - Visit Track';
            
            // تصميم محتوى الإيميل (HTML) مع إدراج الـ QR Code
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-w-md: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; text-align: center; background-color: #f8fafc;">
                <h2 style="color: #2563eb;">Welcome to Visit Track!</h2>
                <p style="color: #64748b; font-size: 16px;">Dear <strong>' . $name . '</strong>,</p>
                <p style="color: #64748b; font-size: 16px;">Your visit request has been registered successfully. Please present the QR code below to the security at the gate.</p>
                
                <div style="background: white; padding: 15px; border-radius: 10px; display: inline-block; margin: 20px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $tid . '" alt="QR Code">
                </div>
                
                <p style="font-size: 14px; color: #94a3b8; text-transform: uppercase;">Tracking ID</p>
                <h3 style="color: #1e293b; font-size: 24px; letter-spacing: 2px; margin-top: 0;">' . $tid . '</h3>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px;">Planned Visit: ' . date('h:i A', strtotime($arrival)) . ' to ' . date('h:i A', strtotime($departure)) . '</p>
            </div>';

            $mail->send();
        } catch (Exception $e) {
            // error_log("Mailer Error: {$mail->ErrorInfo}");
        }
        // ==========================================
        // نهاية كود الإيميل
        // ==========================================

        // توجيه الزائر لصفحة النجاح
        header("Location: register.php?success=1&tid=" . $tid);
        exit();
    }
}
?>