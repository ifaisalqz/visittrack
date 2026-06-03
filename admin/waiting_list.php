<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
include '../includes/db.php';
include '../includes/mailer.php';

// ثوابت الوقت
define('COMPANY_OPEN',   7 * 60);       // 7:00 AM  بالدقائق
define('COMPANY_CLOSE',  15 * 60 + 30); // 3:30 PM  بالدقائق
define('GRACE_MINUTES',  15);           // نافذة السماح

// تحويل وقت HH:MM:SS إلى دقائق من منتصف الليل
function toMinutes($timeStr) {
    $p = explode(':', $timeStr);
    return (int)$p[0] * 60 + (int)$p[1];
}

// حالة النافذة الزمنية للزائر
// ترجع: ['ok'=>bool, 'label'=>string, 'color'=>string, 'diff'=>int]
function timeWindowStatus($arrivalTime) {
    $now     = (int)date('H') * 60 + (int)date('i');
    $arrival = toMinutes($arrivalTime);
    $early   = $arrival - GRACE_MINUTES;
    $late    = $arrival + GRACE_MINUTES;

    if ($now > COMPANY_CLOSE) {
        return ['ok'=>false, 'label'=>'الشركة مغلقة', 'color'=>'red', 'diff'=>0];
    }
    if ($now < $early) {
        $diff = $early - $now;
        return ['ok'=>false, 'label'=> 'مبكر ' . $diff . ' د', 'color'=>'yellow', 'diff'=>$diff];
    }
    if ($now > $late) {
        $diff = $now - $late;
        return ['ok'=>false, 'label'=> 'تأخر ' . $diff . ' د', 'color'=>'orange', 'diff'=>-$diff];
    }
    return ['ok'=>true, 'label'=>'في الوقت', 'color'=>'green', 'diff'=>0];
}

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
    $stmt = $conn->prepare("SELECT *, arrival_time, departure_time FROM visitors WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $v = $stmt->fetch();

    if ($v) {
        $now    = date('Y-m-d H:i:s');
        $nowMin = (int)date('H') * 60 + (int)date('i');

        if ($_GET['op'] == 'checkin') {
            $arrival  = toMinutes($v['arrival_time']);
            $early    = $arrival - GRACE_MINUTES;
            $isEarly  = $nowMin < $early;
            $isLate   = $nowMin > ($arrival + GRACE_MINUTES);
            $comment  = trim($_POST['comment'] ?? '');

            // مبكر جداً — لا نسمح
            if ($isEarly) {
                $_SESSION['checkin_error'] = [
                    'name' => $v['full_name'],
                    'msg'  => 'مبكر ' . ($early - $nowMin) . ' دقيقة — وقت الدخول ' . date('h:i A', strtotime($v['arrival_time'])),
                ];
                header("Location: waiting_list.php"); exit();
            }

            // متأخر — نحتاج كومنت
            if ($isLate && $comment === '') {
                $_SESSION['need_comment'] = ['id' => $v['id'], 'op' => 'checkin', 'name' => $v['full_name'], 'diff' => $nowMin - ($arrival + GRACE_MINUTES)];
                header("Location: waiting_list.php"); exit();
            }

            $upd = $conn->prepare("UPDATE visitors SET actual_checkin = ?, checkin_note = ? WHERE id = ? AND actual_checkin IS NULL");
            $upd->execute([$now, $comment ?: null, $_GET['id']]);
            if ($upd->rowCount() > 0) @sendCheckEmail($v['email'], $v['full_name'], $v['tracking_id'], 'checkin', $now);

        } elseif ($_GET['op'] == 'checkout') {
            $departure = toMinutes($v['departure_time']);
            $isOverdue = $nowMin > $departure;
            $comment   = trim($_POST['comment'] ?? '');

            // تجاوز وقت المغادرة — نحتاج كومنت
            if ($isOverdue && $comment === '') {
                $_SESSION['need_comment'] = ['id' => $v['id'], 'op' => 'checkout', 'name' => $v['full_name'], 'diff' => $nowMin - $departure];
                header("Location: waiting_list.php"); exit();
            }

            $upd = $conn->prepare("UPDATE visitors SET actual_checkout = ?, checkout_note = ?, status = 'completed' WHERE id = ? AND actual_checkin IS NOT NULL AND actual_checkout IS NULL");
            $upd->execute([$now, $comment ?: null, $_GET['id']]);
            if ($upd->rowCount() > 0) @sendCheckEmail($v['email'], $v['full_name'], $v['tracking_id'], 'checkout', $v['actual_checkin'], $now);
        }
    }
    header("Location: waiting_list.php");
    exit();
}

// جلب رسالة الخطأ والكومنت المطلوب
$checkinError  = $_SESSION['checkin_error'] ?? null;
$needComment   = $_SESSION['need_comment']  ?? null;
unset($_SESSION['checkin_error'], $_SESSION['need_comment']);

$waiting = $conn->query("SELECT * FROM visitors WHERE actual_checkin IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY arrival_time ASC")->fetchAll();
$active  = $conn->query("SELECT * FROM visitors WHERE actual_checkin IS NOT NULL AND actual_checkout IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY actual_checkin DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Facility Access - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }

        // تحديث تلقائي كل ٣٠ ثانية
        let countdown = 30;
        setInterval(() => {
            countdown--;
            const el = document.getElementById('refresh-timer');
            if (el) el.textContent = countdown + 's';
            if (countdown <= 0) location.reload();
        }, 1000);

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
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">Facility Access</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Manage Check-in & Check-out</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Auto-refresh <span id="refresh-timer" class="text-blue-600 dark:text-blue-400">30s</span></span>
                </span>
                <a href="gate.php" target="_blank" class="px-4 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all">
                    شاشة البوابة
                </a>
            </div>
        </header>

        <!-- رسالة خطأ تسجيل الدخول -->
        <?php if ($checkinError): ?>
        <div class="mb-6 flex items-center gap-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-2xl px-6 py-4">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-black text-red-700 dark:text-red-400">تعذّر تسجيل دخول <span class="font-mono"><?php echo htmlspecialchars($checkinError['name']); ?></span></p>
                <p class="text-xs font-bold text-red-500 mt-0.5"><?php echo htmlspecialchars($checkinError['msg']); ?></p>
            </div>
        </div>
        <?php endif; ?>

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

                                <!-- شارة النافذة الزمنية -->
                                <?php
                                    $ws = timeWindowStatus($w['arrival_time']);
                                    $badgeClasses = [
                                        'green'  => 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border-green-200 dark:border-green-500/20',
                                        'yellow' => 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-500/20',
                                        'orange' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                                        'red'    => 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/20',
                                    ];
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[9px] font-black uppercase tracking-widest mb-3 <?php echo $badgeClasses[$ws['color']]; ?>">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <?php echo $ws['label']; ?>
                                </span>

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

                            <?php
                            $nowMin  = (int)date('H') * 60 + (int)date('i');
                            $arrMin  = toMinutes($w['arrival_time']);
                            $early   = $arrMin - GRACE_MINUTES;
                            $late    = $arrMin + GRACE_MINUTES;
                            $isEarly = $nowMin < $early;
                            $isLate  = $nowMin > $late;
                            $isToday = isset($w['visit_date']) && $w['visit_date'] === date('Y-m-d');
                            ?>
                            <?php if (!$isToday): ?>
                                <div class="flex-shrink-0 px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed border border-slate-200 dark:border-slate-700 select-none" title="الزيارة ليست اليوم">Check-in</div>
                            <?php elseif ($isEarly): ?>
                                <div class="flex-shrink-0 px-5 py-2.5 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed border border-yellow-200 dark:border-yellow-500/20 select-none" title="مبكر <?php echo ($early-$nowMin); ?> دقيقة">مبكر <?php echo ($early-$nowMin); ?> د</div>
                            <?php elseif ($isLate): ?>
                                <button onclick="openComment('checkin','<?php echo $w['id']; ?>','<?php echo addslashes($w['full_name']); ?>',<?php echo $nowMin-$late; ?>)"
                                        class="flex-shrink-0 px-5 py-2.5 bg-orange-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-orange-600 active:scale-95 transition-all">
                                    تأخر <?php echo ($nowMin-$late); ?> د
                                </button>
                            <?php else: ?>
                                <a href="?op=checkin&id=<?php echo $w['id']; ?>" class="flex-shrink-0 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 active:scale-95 transition-all">Check-in</a>
                            <?php endif; ?>
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

                            <div class="flex-shrink-0 flex flex-col gap-2">
                                    <?php
                                    $depMin   = toMinutes($a['departure_time']);
                                    $nowMin2  = (int)date('H') * 60 + (int)date('i');
                                    $overdue  = $nowMin2 > $depMin;
                                    ?>
                                    <?php if ($overdue): ?>
                                    <button onclick="openComment('checkout','<?php echo $a['id']; ?>','<?php echo addslashes($a['full_name']); ?>',<?php echo $nowMin2-$depMin; ?>)"
                                            class="px-5 py-2.5 bg-orange-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-orange-600 active:scale-95 transition-all text-center">
                                        Check-out +<?php echo ($nowMin2-$depMin); ?>د
                                    </button>
                                    <?php else: ?>
                                    <a href="?op=checkout&id=<?php echo $a['id']; ?>" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-700 hover:border-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all text-center">Check-out</a>
                                    <?php endif; ?>
                                    <a href="badge.php?id=<?php echo $a['id']; ?>" target="_blank" class="px-5 py-2.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 hover:bg-blue-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all text-center">طباعة البطاقة</a>
                                </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($active) == 0): ?>
                    <div class="p-10 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">Facility is empty</div>
                    <?php endif; ?>
                </div>
            </div>

    <!-- ====== Comment Modal ====== -->
    <div id="commentModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2rem] w-full max-w-md shadow-2xl p-8">
            <h2 class="text-xl font-black text-slate-800 dark:text-white mb-1" id="cmTitle">سبب التأخير</h2>
            <p class="text-xs font-bold text-orange-500 mb-6" id="cmSubtitle"></p>
            <form id="cmForm" method="POST">
                <input type="hidden" name="comment" id="cmCommentHidden">
                <textarea id="cmText" rows="3" placeholder="اكتب السبب هنا..." required
                    class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-orange-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm resize-none mb-4"></textarea>
                <div class="flex gap-3">
                    <button type="button" onclick="closeComment()" class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">إلغاء</button>
                    <button type="button" onclick="submitComment()" class="flex-1 py-3 bg-orange-500 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-600 transition-all">تأكيد</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openComment(op, id, name, diffMins) {
        const modal = document.getElementById('commentModal');
        document.getElementById('cmTitle').textContent =
            op === 'checkin' ? 'سبب التأخير عن الدخول' : 'سبب التأخير عن المغادرة';
        document.getElementById('cmSubtitle').textContent =
            name + ' — تأخر ' + diffMins + ' دقيقة';
        document.getElementById('cmForm').action = '?op=' + op + '&id=' + id;
        document.getElementById('cmText').value = '';
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('cmText').focus(), 100);
    }
    function closeComment() {
        document.getElementById('commentModal').classList.add('hidden');
    }
    function submitComment() {
        const txt = document.getElementById('cmText').value.trim();
        if (!txt) { document.getElementById('cmText').focus(); return; }
        document.getElementById('cmCommentHidden').value = txt;
        document.getElementById('cmForm').submit();
    }
    </script>
</body>
</html>
