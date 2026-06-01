<?php
// أداة تشخيص الإيميل — شغّله مرة واحدة ثم احذفه
// http://localhost/VisitTrack/admin/test_email.php

session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die("Login required.");
}

require_once '../vendor/autoload.php';
include '../includes/db.php';
include '../includes/mailer.php';

$result = [];
$testEmail = isset($_GET['email']) ? $_GET['email'] : '';

if ($testEmail && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {

    // Test 1: plain sendVisitEmail
    $r1 = sendVisitEmail(
        $testEmail, 'Test User',
        'Test Email - Visit Track',
        '<div style="font-family:Arial;padding:20px;"><h2>Test</h2><p>If you see this, email is working.</p></div>'
    );
    $result['sendVisitEmail'] = $r1 ? '✅ Sent' : '❌ Failed';

    // Test 2: buildEmailHtml approved
    try {
        $e = buildEmailHtml('approved', ['name'=>'Test User','tid'=>'TST-0001','arrival'=>'08:00','departure'=>'16:00']);
        $r2 = sendVisitEmail($testEmail, 'Test User', $e['subject'], $e['html']);
        $result['approved_email'] = $r2 ? '✅ Sent' : '❌ Failed';
    } catch (Throwable $ex) {
        $result['approved_email'] = '❌ Exception: ' . $ex->getMessage();
    }

    // Test 3: buildEmailHtml checkin
    try {
        $e = buildEmailHtml('checkin', ['name'=>'Test User','tid'=>'TST-0001','checkin_time'=>date('Y-m-d H:i:s')]);
        $r3 = sendVisitEmail($testEmail, 'Test User', $e['subject'], $e['html']);
        $result['checkin_email'] = $r3 ? '✅ Sent' : '❌ Failed';
    } catch (Throwable $ex) {
        $result['checkin_email'] = '❌ Exception: ' . $ex->getMessage();
    }

}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Email Test</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-10 font-mono">
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-black mb-6">Email Diagnostics</h1>

    <form method="GET" class="flex gap-3 mb-8">
        <input name="email" type="email" value="<?php echo htmlspecialchars($testEmail); ?>"
               placeholder="your@email.com"
               class="flex-1 bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white outline-none focus:border-blue-500">
        <button type="submit" class="px-6 py-3 bg-blue-600 rounded-xl font-black hover:bg-blue-700">Send Test</button>
    </form>

    <?php if ($result): ?>
    <div class="space-y-3">
        <?php foreach ($result as $k => $v): ?>
        <div class="bg-slate-800 rounded-xl px-5 py-4 flex justify-between items-center border border-slate-700">
            <span class="text-slate-400 uppercase text-xs tracking-widest"><?php echo $k; ?></span>
            <span class="font-black"><?php echo $v; ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (in_array('❌ Failed', array_values($result))): ?>
    <div class="mt-6 bg-red-900/30 border border-red-500/30 rounded-xl p-5 text-sm text-red-300">
        <p class="font-black mb-2">Check Apache error log:</p>
        <code>C:\xampp\logs\php_error_log</code><br>
        or <code>C:\xampp\apache\logs\error.log</code>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <p class="mt-8 text-slate-600 text-xs">احذف هذا الملف بعد الانتهاء من التشخيص.</p>
</div>
</body>
</html>
