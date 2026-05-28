<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }

include '../includes/db.php';
include '../includes/mailer.php';

if (isset($_GET['op']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $op = $_GET['op'];
    $current_time = date('Y-m-d H:i:s');

    $v_stmt = $conn->prepare("SELECT full_name, email, tracking_id FROM visitors WHERE id = ?");
    $v_stmt->execute([$id]);
    $visitor = $v_stmt->fetch();

    if ($visitor) {
        if ($op == 'checkin') {
            $conn->prepare("UPDATE visitors SET status = 'approved', actual_checkin = ? WHERE id = ?")->execute([$current_time, $id]);
            
            $subject = 'Successful Check-in Notice - Visit Track';
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; text-align: center; background-color: #f8fafc;">
                <h2 style="color: #2563eb;">Check-in Confirmed</h2>
                <p style="color: #64748b; font-size: 16px;">Dear <strong>' . htmlspecialchars($visitor['full_name']) . '</strong>,</p>
                <p style="color: #64748b; font-size: 16px;">Your entry has been recorded successfully. Welcome to our campus.</p>
                <div style="background: #eff6ff; padding: 15px; border-radius: 10px; display: inline-block; margin: 20px 0; border: 1px solid #bfdbfe;">
                    <span style="font-size: 14px; color: #1e40af; font-weight: bold;">Check-in Time: </span>
                    <strong style="font-size: 16px; color: #1e3a8a;">' . date('h:i A') . '</strong>
                </div>
                <p style="font-size: 12px; color: #94a3b8;">Tracking ID: ' . $visitor['tracking_id'] . '</p>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px;">Please remember to scan your QR code again upon departure.</p>
            </div>';

            sendVisitEmail($visitor['email'], $visitor['full_name'], $subject, $body);

        } elseif ($op == 'checkout') {
            $conn->prepare("UPDATE visitors SET status = 'expired', actual_checkout = ? WHERE id = ?")->execute([$current_time, $id]);
            
            $subject = 'Successful Check-out Notice - Visit Track';
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; text-align: center; background-color: #f8fafc;">
                <h2 style="color: #475569;">Check-out Confirmed</h2>
                <p style="color: #64748b; font-size: 16px;">Dear <strong>' . htmlspecialchars($visitor['full_name']) . '</strong>,</p>
                <p style="color: #64748b; font-size: 16px;">Thank you for your visit. Your check-out has been safely recorded.</p>
                <div style="background: #f1f5f9; padding: 15px; border-radius: 10px; display: inline-block; margin: 20px 0; border: 1px solid #cbd5e1;">
                    <span style="font-size: 14px; color: #334155; font-weight: bold;">Check-out Time: </span>
                    <strong style="font-size: 16px; color: #0f172a;">' . date('h:i A') . '</strong>
                </div>
                <p style="font-size: 12px; color: #94a3b8;">Tracking ID: ' . $visitor['tracking_id'] . '</p>
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px;">Visit Track - Secure Visitor Management Platform</p>
            </div>';

            sendVisitEmail($visitor['email'], $visitor['full_name'], $subject, $body);
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
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .font-style-normal { font-style: normal !important; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen transition-colors duration-300">
    
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-12 overflow-x-hidden">
        
        <header class="mb-6">
            <h1 class="text-3xl font-black tracking-tight font-style-normal italic text-slate-800 dark:text-white">Waiting for Check-in</h1>
            <p class="text-slate-400 mt-1 font-medium text-xs font-style-normal">Approved visitors expected today</p>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden mb-12">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Info</th>
                        <th class="p-6">Destination</th>
                        <th class="p-6">Expected Time</th>
                        <th class="p-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($waiting as $w): ?>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base font-style-normal"><?php echo htmlspecialchars($w['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-0.5">ID: <?php echo htmlspecialchars($w['national_id']); ?> | TID: <?php echo htmlspecialchars($w['tracking_id']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-style-normal">Host: <span class="text-blue-600 dark:text-blue-400 font-black"><?php echo htmlspecialchars($w['host_name']); ?></span></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-black text-slate-600 dark:text-slate-400 font-style-normal"><?php echo date('h:i A', strtotime($w['arrival_time'])); ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <a href="?op=checkin&id=<?php echo $w['id']; ?>" class="px-5 py-2.5 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-green-700 transition font-style-normal">Confirm Check-in</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($waiting)) echo "<tr><td colspan='4' class='p-8 text-center text-slate-400 italic text-sm'>No waiting visitors</td></tr>"; ?>
                </tbody>
            </table>
        </div>

        <header class="mb-6">
            <h1 class="text-3xl font-black tracking-tight font-style-normal italic text-slate-800 dark:text-white">Active Inside Campus</h1>
            <p class="text-slate-400 mt-1 font-medium text-xs font-style-normal">Visitors currently inside the facility</p>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Info</th>
                        <th class="p-6">Host</th>
                        <th class="p-6">Checked In At</th>
                        <th class="p-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($active as $a): ?>
                    <tr class="hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base font-style-normal"><?php echo htmlspecialchars($a['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Mob: <?php echo htmlspecialchars($a['phone']); ?> | Vehicle: <?php echo !empty($a['vehicle_details']) ? htmlspecialchars($a['vehicle_details']) : 'None'; ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-style-normal"><?php echo htmlspecialchars($a['host_name']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-black text-blue-600 dark:text-blue-400 font-style-normal"><?php echo date('h:i A', strtotime($a['actual_checkin'])); ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <a href="?op=checkout&id=<?php echo $a['id']; ?>" class="px-5 py-2.5 bg-slate-800 dark:bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-red-700 transition font-style-normal">Confirm Check-out</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($active)) echo "<tr><td colspan='4' class='p-8 text-center text-slate-400 italic text-sm'>No active visitors inside currently</td></tr>"; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>