<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
include '../includes/db.php';

$totalQuery     = $conn->query("SELECT COUNT(*) FROM visitors WHERE status IN ('completed', 'rejected', 'expired') OR actual_checkout IS NOT NULL")->fetchColumn();
$completedQuery = $conn->query("SELECT COUNT(*) FROM visitors WHERE status = 'completed' OR actual_checkout IS NOT NULL")->fetchColumn();
$rejectedQuery  = $conn->query("SELECT COUNT(*) FROM visitors WHERE status = 'rejected'")->fetchColumn();

// نجيب كل السجلات المنتهية: سواء status = completed/rejected/expired
// أو عندهم actual_checkout (زوار اتسووا قبل الإصلاح)
$logs = $conn->query("
    SELECT * FROM visitors 
    WHERE status IN ('completed', 'rejected', 'expired')
       OR actual_checkout IS NOT NULL
    ORDER BY id DESC
")->fetchAll();

// مدة الزيارة بشكل مقروء مع الثواني
function formatDuration($checkin, $checkout) {
    if (empty($checkin) || empty($checkout)) return null;
    $total = strtotime($checkout) - strtotime($checkin);
    if ($total <= 0) return null;
    $h = floor($total / 3600);
    $m = floor(($total % 3600) / 60);
    $s = $total % 60;
    $label = ($h > 0 ? $h . 'h ' : '') . $m . 'm ' . $s . 's';
    return ['label' => $label, 'total' => $total];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History Logs - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] flex min-h-screen text-slate-800 dark:text-slate-300 transition-colors duration-300 overflow-x-hidden">
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none -z-10"></div>
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-8 md:p-12 transition-all z-10 relative">
        <header class="mb-12">
            <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">History Logs</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Comprehensive visitor archive</p>
        </header>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800/80 shadow-xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Total Archived</p>
                <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $totalQuery; ?></div>
            </div>
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl p-8 rounded-[2rem] border border-green-200 dark:border-green-500/20 shadow-xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-green-600 dark:text-green-500 mb-2">Completed Visits</p>
                <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $completedQuery; ?></div>
            </div>
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl p-8 rounded-[2rem] border border-red-200 dark:border-red-500/20 shadow-xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-red-600 dark:text-red-500 mb-2">Rejected Requests</p>
                <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $rejectedQuery; ?></div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700/50">
                        <tr class="uppercase text-[10px] font-black tracking-widest text-slate-500">
                            <th class="p-6">Visitor & Date</th>
                            <th class="p-6">Visit Details</th>
                            <th class="p-6">Check-in / Check-out</th>
                            <th class="p-6">Duration</th>
                            <th class="p-6 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        <?php foreach($logs as $l):
                            $duration = formatDuration($l['actual_checkin'], $l['actual_checkout']);
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">

                            <!-- Visitor & Date -->
                            <td class="p-6">
                                <div class="font-black text-slate-800 dark:text-white text-base mb-1"><?php echo htmlspecialchars($l['full_name']); ?></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"><?php echo date('M d, Y', strtotime($l['created_at'])); ?></div>
                            </td>

                            <!-- Visit Details -->
                            <td class="p-6">
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($l['purpose']); ?></div>
                                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Host: <?php echo htmlspecialchars($l['host_name']); ?></div>
                            </td>

                            <!-- Check-in / Check-out times -->
                            <td class="p-6">
                                <?php if (!empty($l['actual_checkin'])): ?>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                        <span class="text-[9px] text-slate-400 uppercase tracking-widest block mb-0.5">In</span>
                                        <?php echo date('h:i:s A', strtotime($l['actual_checkin'])); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($l['actual_checkout'])): ?>
                                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-2">
                                        <span class="text-[9px] text-slate-400 uppercase tracking-widest block mb-0.5">Out</span>
                                        <?php echo date('h:i:s A', strtotime($l['actual_checkout'])); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (empty($l['actual_checkin']) && empty($l['actual_checkout'])): ?>
                                    <div class="text-sm font-bold text-slate-400 dark:text-slate-600">—</div>
                                <?php endif; ?>
                            </td>

                            <!-- Duration -->
                            <td class="p-6">
                                <?php if ($duration): ?>
                                    <div class="text-sm font-black text-slate-800 dark:text-white font-mono"><?php echo $duration['label']; ?></div>
                                    <div class="text-[10px] font-bold text-slate-500 mt-1"><?php echo number_format($duration['total']); ?> sec total</div>
                                <?php else: ?>
                                    <div class="text-sm font-bold text-slate-400 dark:text-slate-600">—</div>
                                <?php endif; ?>
                            </td>

                            <!-- Status badge -->
                            <td class="p-6 text-center">
                                <?php
                                    $badge = 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-500 border-slate-200 dark:border-slate-500/20';
                                    if ($l['status'] == 'completed' || !empty($l['actual_checkout'])) $badge = 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-500 border-green-200 dark:border-green-500/20';
                                    if ($l['status'] == 'rejected') $badge = 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-500 border-red-200 dark:border-red-500/20';
                                    $label = !empty($l['actual_checkout']) && $l['status'] != 'rejected' ? 'completed' : $l['status'];
                                ?>
                                <span class="inline-block px-4 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest <?php echo $badge; ?>"><?php echo $label; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($logs) == 0): ?>
                        <tr><td colspan="5" class="p-12 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">No history logs found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
