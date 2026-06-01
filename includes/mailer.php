<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) { require_once $autoloadPath; }

// =====================================================
// sendVisitEmail — الدالة الأساسية لإرسال الإيميل
// =====================================================
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

// =====================================================
// buildEmailHtml — قالب موحد لكل أنواع الإيميلات
//
// $type:  'submitted' | 'approved' | 'rejected' | 'checkin' | 'checkout'
// $data:  [
//   'name'          => string   (required)
//   'tid'           => string   (required)
//   'arrival'       => string   (optional, for submitted/approved)
//   'departure'     => string   (optional, for submitted/approved)
//   'checkin_time'  => string   (optional, for checkin/checkout)
//   'checkout_time' => string   (optional, for checkout)
// ]
// =====================================================
function buildEmailHtml($type, $data) {
    $palette = [
        'submitted' => ['color' => '#6366f1', 'title' => 'Request Received'],
        'approved'  => ['color' => '#16a34a', 'title' => 'Visit Approved'],
        'rejected'  => ['color' => '#dc2626', 'title' => 'Visit Declined'],
        'checkin'   => ['color' => '#2563eb', 'title' => 'Checked In'],
        'checkout'  => ['color' => '#0d9488', 'title' => 'Visit Complete'],
    ];

    $messages = [
        'submitted' => 'Your visit request has been received and is pending approval. You will be notified by email once it is reviewed.',
        'approved'  => 'Great news! Your visit request has been approved. Please present the QR code below to security at the gate.',
        'rejected'  => 'We regret to inform you that your visit request has not been approved at this time. Please contact your host for more information.',
        'checkin'   => 'You have successfully checked in. Welcome, and have a productive visit!',
        'checkout'  => 'Thank you for your visit. You have successfully checked out. We hope to see you again soon.',
    ];

    $subjects = [
        'submitted' => 'Visit Request Received - Visit Track',
        'approved'  => 'Your Visit Request Has Been Approved - Visit Track',
        'rejected'  => 'Visit Request Update - Visit Track',
        'checkin'   => "You've Checked In - Visit Track",
        'checkout'  => 'Thank You for Visiting - Visit Track',
    ];

    $p       = $palette[$type]  ?? ['color' => '#334155', 'title' => 'Visit Track'];
    $message = $messages[$type] ?? '';
    $color   = $p['color'];
    $title   = $p['title'];
    $name    = htmlspecialchars($data['name'] ?? '');
    $tid     = htmlspecialchars($data['tid']  ?? '');

    // ---- QR code (submitted + approved only) ----
    $qrSection = '';
    if (in_array($type, ['submitted', 'approved'])) {
        $qrSection = '
        <div style="text-align:center;margin:24px 0;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($data['tid'] ?? '') . '&color=0f172a"
                 style="border-radius:12px;border:2px solid #e2e8f0;padding:8px;background:#ffffff;"
                 width="150" height="150" alt="QR Code">
        </div>';
    }

    // ---- Info boxes ----
    $infoSection = '';

    // Arrival / Departure (submitted + approved)
    if (in_array($type, ['submitted', 'approved']) && !empty($data['arrival']) && !empty($data['departure'])) {
        $infoSection .= '
        <div style="display:flex;gap:10px;margin-bottom:12px;">
            <div style="flex:1;background:#fff;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;margin-bottom:5px;">Arrival</div>
                <div style="font-size:15px;font-weight:900;color:#1e293b;">' . date('h:i A', strtotime($data['arrival'])) . '</div>
            </div>
            <div style="flex:1;background:#fff;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;margin-bottom:5px;">Departure</div>
                <div style="font-size:15px;font-weight:900;color:#1e293b;">' . date('h:i A', strtotime($data['departure'])) . '</div>
            </div>
        </div>';
    }

    // Check-in time
    if ($type === 'checkin' && !empty($data['checkin_time'])) {
        $infoSection .= '
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;margin-bottom:12px;">
            <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;margin-bottom:5px;">Check-in Time</div>
            <div style="font-size:15px;font-weight:900;color:#1e293b;">' . date('h:i:s A', strtotime($data['checkin_time'])) . '</div>
        </div>';
    }

    // Check-out time + duration
    if ($type === 'checkout' && !empty($data['checkout_time'])) {
        $duration = '';
        if (!empty($data['checkin_time'])) {
            $secs = strtotime($data['checkout_time']) - strtotime($data['checkin_time']);
            if ($secs > 0) {
                $h = floor($secs / 3600);
                $m = floor(($secs % 3600) / 60);
                $s = $secs % 60;
                $duration = ($h > 0 ? $h . 'h ' : '') . $m . 'm ' . $s . 's';
            }
        }
        $infoSection .= '
        <div style="display:flex;gap:10px;margin-bottom:12px;">
            <div style="flex:1;background:#fff;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;margin-bottom:5px;">Checked Out</div>
                <div style="font-size:15px;font-weight:900;color:#1e293b;">' . date('h:i:s A', strtotime($data['checkout_time'])) . '</div>
            </div>
            ' . ($duration ? '
            <div style="flex:1;background:#fff;border-radius:10px;padding:14px;border:1px solid #e2e8f0;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:2px;text-transform:uppercase;margin-bottom:5px;">Duration</div>
                <div style="font-size:15px;font-weight:900;color:#1e293b;">' . $duration . '</div>
            </div>' : '') . '
        </div>';
    }

    $html = '
    <div style="font-family:\'Helvetica Neue\',Arial,sans-serif;max-width:500px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

        <!-- Header -->
        <div style="background:' . $color . ';padding:36px 40px;text-align:center;">
            <div style="font-size:10px;font-weight:900;color:rgba(255,255,255,0.65);letter-spacing:5px;text-transform:uppercase;margin-bottom:10px;">VISIT TRACK</div>
            <div style="font-size:26px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">' . $title . '</div>
        </div>

        <!-- Body -->
        <div style="padding:32px 40px;background:#f8fafc;">
            <p style="color:#64748b;font-size:15px;margin:0 0 6px;">Dear <strong style="color:#1e293b;">' . $name . '</strong>,</p>
            <p style="color:#64748b;font-size:15px;margin:0 0 24px;line-height:1.6;">' . $message . '</p>

            ' . $qrSection . '
            ' . $infoSection . '

            <!-- Tracking ID -->
            <div style="background:#ffffff;border-radius:12px;padding:16px 20px;text-align:center;border:1px solid #e2e8f0;">
                <div style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:3px;text-transform:uppercase;margin-bottom:6px;">Tracking ID</div>
                <div style="font-size:18px;font-weight:900;color:' . $color . ';letter-spacing:3px;font-family:monospace;">' . $tid . '</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:18px 40px;background:#f1f5f9;text-align:center;border-top:1px solid #e2e8f0;">
            <p style="color:#94a3b8;font-size:11px;font-weight:600;letter-spacing:1px;margin:0;">© ' . date('Y') . ' VISIT TRACK &nbsp;·&nbsp; All rights reserved.</p>
        </div>

    </div>';

    return ['html' => $html, 'subject' => $subjects[$type] ?? 'Visit Track Notification'];
}
