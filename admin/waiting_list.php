<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
include '../includes/db.php';
include '../includes/mailer.php';

function sendCheckEmail($toEmail, $toName, $tid, $type, $checkin = null, $checkout = null) {
    $email = buildEmailHtml($type, [
        'name'          => $toName,
        'tid'           => $tid,
        'checkin_time'  => $checkin,
        'checkout_time' => $checkout,
    ]);
    return sendVisitEmail($toEmail, $toName, $email['subject'], $email['html']);
}

if (isset($_GET['op']) && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT email, full_name, tracking_id, actual_checkin FROM visitors WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $v = $stmt->fetch();
    if ($v) {
        $now = date('Y-m-d H:i:s');
        if ($_GET['op'] == 'checkin') {
            $upd = $conn->prepare("UPDATE visitors SET actual_checkin = ? WHERE id = ? AND actual_checkin IS NULL");
            $upd->execute([$now, $_GET['id']]);
            if ($upd->rowCount() > 0) @sendCheckEmail($v['email'], $v['full_name'], $v['tracking_id'], 'checkin', $now);
        } elseif ($_GET['op'] == 'checkout') {
            $upd = $conn->prepare("UPDATE visitors SET actual_checkout = ?, status = 'completed' WHERE id = ? AND actual_checkin IS NOT NULL AND actual_checkout IS NULL");
            $upd->execute([$now, $_GET['id']]);
            if ($upd->rowCount() > 0) @sendCheckEmail($v['email'], $v['full_name'], $v['tracking_id'], 'checkout', $v['actual_checkin'], $now);
        }
    }
    header("Location: waiting_list.php");
    exit();
}

$waiting = $conn->query("SELECT * FROM visitors WHERE actual_checkin IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY arrival_time ASC")->fetchAll();
$active  = $conn->query("SELECT * FROM visitors WHERE actual_checkin IS NOT NULL AND actual_checkout IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY actual_checkin DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Facility Access - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }

        function toggleDetails(btn) {
            const panel = btn.parentElement.querySelector('.details-panel');
            const arrow  = btn.querySelector('.arrow');
            const isOpen = !panel.classList.contains('hidden');
            panel.classList.toggle('hidden', isOpen);
            arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
            btn.querySelector('.btn-label').textContent = isOpen ? 'Show details' : 'Hide details';
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .arrow { transition: transform 0.2s ease; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] flex min-h-screen text-slate-800 dark:text-slate-300 transition-colors duration-300 overflow-x-hidden">
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none -z-10"></div>
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-8 md:p-12 transition-all z-10 relative">
        <header class="mb-12">
            <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">Facility Access</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Manage Check-in & Check-out</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

            <!-- ======= Expected Visitors ======= -->
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl overflow-hidden flex flex-col">
                <div class="p-8 border-b border-slate-200 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/20 flex items-center justify-between">
                    <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-widest">Expected Visitors</h2>
                    <span class="px-3 py-1 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-500/20 rounded-full text-[10px] font-black"><?php echo count($waiting); ?> Waiting</span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/50 flex-1">
                    <?php foreach($waiting as $w): ?>
                    <div class="p-6 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                        <div class="flex items-start justify-between gap-4">

                            <!-- QR on the left -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?php echo urlencode($w['tracking_id']); ?>&color=0f172a"
                                 class="flex-shrink-0 rounded-xl border border-slate-200 dark:border-slate-700 bg-white p-1.5" width="90" height="90" alt="QR">

                            <div class="flex-1 min-w-0">
                                <!-- Basic: always visible -->
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-black text-slate-800 dark:text-white text-base"><?php echo htmlspecialchars($w['full_name']); ?></span>
                                    <span class="text-[9px] font-black font-mono text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 rounded-lg border border-blue-100 dark:border-blue-500/20"><?php echo htmlspecialchars($w['tracking_id']); ?></span>
                                </div>
                                <p class="text-xs font-black text-blue-600 dark:text-blue-400 mb-3">
                                    <?php echo date('h:i A', strtotime($w['arrival_time'])); ?>
                                    <span class="text-slate-400 mx-1">→</span>
                                    <?php echo date('h:i A', strtotime($w['departure_time'])); ?>
                                </p>

                                <!-- Toggle button -->
                                <button onclick="toggleDetails(this)" class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 uppercase tracking-widest transition-colors">
                                    <svg class="arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    <span class="btn-label">Show details</span>
                                </button>

                                <!-- Expandable details -->
                                <div class="details-panel hidden mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-x-6 gap-y-3">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">National ID</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['national_id']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Phone</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['phone']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Host</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['host_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Purpose</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['purpose']); ?></p>
                                    </div>
                                    <?php if (!empty($w['vehicle_details'])): ?>
                                    <div class="col-span-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Vehicle</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['vehicle_details']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-span-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Email</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($w['email']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <a href="?op=checkin&id=<?php echo $w['id']; ?>" class="flex-shrink-0 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 active:scale-95 transition-all">Check-in</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($waiting) == 0): ?>
                    <div class="p-10 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">No visitors waiting</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ======= Currently Inside ======= -->
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-blue-100 dark:border-blue-900/30 shadow-xl overflow-hidden flex flex-col relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 blur-[50px] pointer-events-none"></div>
                <div class="p-8 border-b border-slate-200 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/20 flex items-center justify-between relative z-10">
                    <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-widest">Currently Inside</h2>
                    <span class="px-3 py-1 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/20 rounded-full text-[10px] font-black animate-pulse"><?php echo count($active); ?> Active</span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800/50 flex-1 relative z-10">
                    <?php foreach($active as $a): ?>
                    <div class="p-6 hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                        <div class="flex items-start justify-between gap-4">

                            <!-- QR on the left -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?php echo urlencode($a['tracking_id']); ?>&color=0f172a"
                                 class="flex-shrink-0 rounded-xl border border-slate-200 dark:border-slate-700 bg-white p-1.5" width="90" height="90" alt="QR">

                            <div class="flex-1 min-w-0">
                                <!-- Basic: always visible -->
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-black text-slate-800 dark:text-white text-base"><?php echo htmlspecialchars($a['full_name']); ?></span>
                                    <span class="text-[9px] font-black font-mono text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 rounded-lg border border-blue-100 dark:border-blue-500/20"><?php echo htmlspecialchars($a['tracking_id']); ?></span>
                                </div>
                                <p class="text-xs font-black text-green-600 dark:text-green-400 mb-3">
                                    Entered: <?php echo date('h:i:s A', strtotime($a['actual_checkin'])); ?>
                                </p>

                                <!-- Toggle button -->
                                <button onclick="toggleDetails(this)" class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 uppercase tracking-widest transition-colors">
                                    <svg class="arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    <span class="btn-label">Show details</span>
                                </button>

                                <!-- Expandable details -->
                                <div class="details-panel hidden mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-x-6 gap-y-3">
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">National ID</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['national_id']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Phone</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['phone']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Host</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['host_name']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Purpose</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['purpose']); ?></p>
                                    </div>
                                    <?php if (!empty($a['vehicle_details'])): ?>
                                    <div class="col-span-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Vehicle</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['vehicle_details']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-span-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Email</p>
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($a['email']); ?></p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Planned Time</p>
                                        <p class="text-xs font-black text-blue-600 dark:text-blue-400">
                                            <?php echo date('h:i A', strtotime($a['arrival_time'])); ?>
                                            <span class="text-slate-400 mx-1">→</span>
                                            <?php echo date('h:i A', strtotime($a['departure_time'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <a href="?op=checkout&id=<?php echo $a['id']; ?>" class="flex-shrink-0 px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-700 hover:border-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">Check-out</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($active) == 0): ?>
                    <div class="p-10 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">Facility is empty</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</body>
</html>
