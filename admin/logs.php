<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

$stats = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected FROM visitors WHERE status IN ('expired', 'rejected')")->fetch();
$logs = $conn->query("SELECT *, TIMESTAMPDIFF(SECOND, actual_checkin, actual_checkout) as total_seconds FROM visitors WHERE status IN ('expired', 'rejected') ORDER BY actual_checkout DESC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History - Visit Track</title>
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

    <main class="flex-1 p-12 overflow-x-hidden text-slate-800 dark:text-white">
        
        <header class="mb-10 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-black tracking-tight">Visitor Archive</h1>
                <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em]">Detailed history logs</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 text-center uppercase tracking-widest text-[10px] font-black">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800">
                <p class="text-slate-400 mb-2">Total Logs</p>
                <h3 class="text-4xl font-black tracking-tighter text-slate-800 dark:text-white"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-2 bg-green-500"></div>
                <p class="text-green-600 dark:text-green-500 mb-2">Completed</p>
                <h3 class="text-4xl font-black text-green-600 dark:text-green-400 tracking-tighter"><?php echo (int)$stats['completed']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-2 bg-red-500"></div>
                <p class="text-red-600 dark:text-red-500 mb-2">Rejected</p>
                <h3 class="text-4xl font-black text-red-600 dark:text-red-400 tracking-tighter"><?php echo (int)$stats['rejected']; ?></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6">Visitor Details</th>
                        <th class="p-6">Dest. Info</th>
                        <th class="p-6 text-center">Status</th>
                        <th class="p-6">Check-In / Out</th>
                        <th class="p-6 text-right">Precise Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <?php foreach($logs as $r): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-200">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base tracking-tight"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-500 mt-1">ID: <?php echo htmlspecialchars($r['national_id']); ?></div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest opacity-70 mt-1">TID: <?php echo $r['tracking_id']; ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Host: <span class="font-black text-blue-600 dark:text-blue-400"><?php echo htmlspecialchars($r['host_name']); ?></span></div>
                            <div class="text-[11px] text-slate-400 font-medium mt-1">Purpose: <?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest <?php echo $r['status']=='expired' ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400'; ?>"><?php echo $r['status'] == 'expired' ? 'Completed' : $r['status']; ?></span>
                        </td>
                        <td class="p-6">
                            <?php if($r['actual_checkin']): ?>
                                <div class="space-y-1.5 font-bold text-[11px]">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> IN: <?php echo date('h:i:s A', strtotime($r['actual_checkin'])); ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> OUT: <?php echo $r['actual_checkout'] ? date('h:i:s A', strtotime($r['actual_checkout'])) : 'N/A'; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-600 italic text-[11px] font-bold">No Entry</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6 text-right">
                            <?php if($r['actual_checkout']): ?>
                                <div class="inline-block px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-100 dark:border-blue-900/50">
                                    <div class="font-black text-blue-600 dark:text-blue-400 text-xs tracking-wide">
                                        <?php 
                                            $ts = $r['total_seconds'];
                                            $h = floor($ts / 3600); 
                                            $m = floor(($ts % 3600) / 60); 
                                            $s = $ts % 60;
                                            echo ($h > 0 ? "{$h}h " : "") . ($m > 0 ? "{$m}m " : "") . "{$s}s"; 
                                        ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-700 font-bold">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>