<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; 
include '../includes/db.php';

if (isset($_GET['op']) && isset($_GET['id'])) {
    $id = (int)$_GET['id']; $op = $_GET['op']; $current_time = date('Y-m-d H:i:s');
    $v_stmt = $conn->prepare("SELECT full_name, email, tracking_id FROM visitors WHERE id = ?");
    $v_stmt->execute([$id]); $visitor = $v_stmt->fetch();

    if ($visitor) {
        if ($op == 'checkin') {
            $conn->prepare("UPDATE visitors SET status = 'approved', actual_checkin = ? WHERE id = ?")->execute([$current_time, $id]);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP(); $mail->Host = 'smtp.office365.com'; $mail->SMTPAuth = true;
                $mail->Username = 'noreply@faisal.biz'; $mail->Password = 'bhwvxcvzsqmvnsgj';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
                $mail->setFrom('noreply@faisal.biz', 'Visit Track System'); $mail->addAddress($visitor['email'], $visitor['full_name']); 
                $mail->isHTML(true); $mail->Subject = 'Check-in Confirmed';
                $mail->Body = '<div style="font-family: Arial; padding: 20px;"><h2>Check-in Successful</h2></div>';
                $mail->send();
            } catch (Exception $e) {}
        } elseif ($op == 'checkout') {
            $conn->prepare("UPDATE visitors SET status = 'expired', actual_checkout = ? WHERE id = ?")->execute([$current_time, $id]);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP(); $mail->Host = 'smtp.office365.com'; $mail->SMTPAuth = true;
                $mail->Username = 'noreply@faisal.biz'; $mail->Password = 'bhwvxcvzsqmvnsgj';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
                $mail->setFrom('noreply@faisal.biz', 'Visit Track System'); $mail->addAddress($visitor['email'], $visitor['full_name']); 
                $mail->isHTML(true); $mail->Subject = 'Check-out Confirmed';
                $mail->Body = '<div style="font-family: Arial; padding: 20px;"><h2>Check-out Successful</h2></div>';
                $mail->send();
            } catch (Exception $e) {}
        }
    }
    header("Location: waiting_list.php"); exit();
}

$waiting = $conn->query("SELECT * FROM visitors WHERE status = 'approved' AND actual_checkin IS NULL ORDER BY arrival_time ASC")->fetchAll();
$active = $conn->query("SELECT * FROM visitors WHERE status = 'approved' AND actual_checkin IS NOT NULL AND actual_checkout IS NULL ORDER BY actual_checkin DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waiting & Active - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark'); }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen transition-colors duration-300">
    
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-12 overflow-x-hidden">
        
        <header class="mb-8 text-slate-800 dark:text-white">
            <h1 class="text-3xl font-black tracking-tight">Waiting for Check-in</h1>
            <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em]">Expected visitors</p>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden mb-12">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Info</th>
                        <th class="p-6">Destination</th>
                        <th class="p-6">Expected Time</th>
                        <th class="p-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <?php foreach($waiting as $w): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-200">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base tracking-tight"><?php echo htmlspecialchars($w['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-1">ID: <?php echo htmlspecialchars($w['national_id']); ?> | TID: <?php echo htmlspecialchars($w['tracking_id']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Host: <span class="text-blue-600 dark:text-blue-400 font-black"><?php echo htmlspecialchars($w['host_name']); ?></span></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-black text-slate-600 dark:text-slate-400 tracking-wide"><?php echo date('h:i A', strtotime($w['arrival_time'])); ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <a href="?op=checkin&id=<?php echo $w['id']; ?>" class="px-5 py-3 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-green-700 hover:shadow-lg transition-all">Confirm Check-in</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($waiting)) echo "<tr><td colspan='4' class='p-10 text-center text-slate-400 font-medium'>No waiting visitors</td></tr>"; ?>
                </tbody>
            </table>
        </div>

        <header class="mb-8 text-slate-800 dark:text-white">
            <h1 class="text-3xl font-black tracking-tight">Active Inside Campus</h1>
            <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em]">Currently inside</p>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Info</th>
                        <th class="p-6">Host</th>
                        <th class="p-6">Checked In At</th>
                        <th class="p-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <?php foreach($active as $a): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-200">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base tracking-tight"><?php echo htmlspecialchars($a['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-1">Mob: <?php echo htmlspecialchars($a['phone']); ?> | Vehicle: <?php echo !empty($a['vehicle_details']) ? htmlspecialchars($a['vehicle_details']) : 'None'; ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['host_name']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-black text-blue-600 dark:text-blue-400 tracking-wide"><?php echo date('h:i A', strtotime($a['actual_checkin'])); ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <a href="?op=checkout&id=<?php echo $a['id']; ?>" class="px-5 py-3 bg-slate-800 dark:bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-slate-900 dark:hover:bg-red-700 transition-all">Confirm Check-out</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($active)) echo "<tr><td colspan='4' class='p-10 text-center text-slate-400 font-medium'>No active visitors currently</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>