<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) { require_once $autoloadPath; }

function sendVisitEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@faisal.biz'; 
        $mail->Password   = 'bhwvxcvzsqmvnsgj';    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply@faisal.biz', 'Visit Track System');
        $mail->addAddress($toEmail, $toName); 
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}
?>