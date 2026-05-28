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
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .font-style-normal { font-style: normal !important; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen transition-colors duration-300">
    
    <aside class="w-72 bg-slate-900 dark:bg-black text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl">
        <div class="mb-12">
            <h2 class="text-2xl font-black text-blue-400 tracking-tight font-style-normal">VISIT TRACK</h2>
            <p class="uppercase tracking-[0.3em] mt-1 text-slate-500 font-black text-[11px] font-style-normal">Admin Panel</p>
        </div>
        <nav class="space-y-3 flex-1 text-sm">
            <a href="index.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal">Live Requests</a>
            <a href="waiting_list.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal">Waiting & Active</a>
            <a href="logs.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg font-bold font-style-normal italic">History Logs</a>
            <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest text-left font-style-normal">🌓 Mode</button>
        </nav>
        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest font-style-normal">Logout</a>
    </aside>

    <main class="flex-1 p-12 text-slate-800 dark:text-white overflow-x-hidden">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-black tracking-tight font-style-normal italic">Visitor History Archive</h1>
                <p class="text-slate-400 mt-2 font-medium font-style-normal">Detailed archive of past facility visits</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 text-center uppercase tracking-widest text-[10px] font-black font-style-normal">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800">
                <p class="text-slate-400 mb-1">Total Logs</p>
                <h3 class="text-3xl font-black italic tracking-tighter text-slate-800 dark:text-white"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border-l-4 border-l-green-500 dark:border-slate-800">
                <p class="text-green-600 mb-1">Completed</p>
                <h3 class="text-3xl font-black text-green-600 italic tracking-tighter"><?php echo (int)$stats['completed']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border-l-4 border-l-red-500 dark:border-slate-800">
                <p class="text-red-600 mb-1">Rejected</p>
                <h3 class="text-3xl font-black text-red-600 italic tracking-tighter"><?php echo (int)$stats['rejected']; ?></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6">Visitor Details</th>
                        <th class="p-6">Dest. Info</th>
                        <th class="p-6">Vehicle Logs</th>
                        <th class="p-6">Status</th>
                        <th class="p-6">Logged Date</th>
                        <th class="p-6">Check-In / Out</th>
                        <th class="p-6 text-right">Precise Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($logs as $r): ?>
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-lg tracking-tight font-style-normal italic"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-500 font-style-normal mt-0.5">ID: <?php echo htmlspecialchars($r['national_id']); ?> | Mob: <?php echo htmlspecialchars($r['phone']); ?></div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-style-normal opacity-60 italic mt-0.5">Track ID: <?php echo $r['tracking_id']; ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Host: <span class="font-black text-blue-600 dark:text-blue-400"><?php echo htmlspecialchars($r['host_name']); ?></span></div>
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">Purpose: <?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6">
                            <?php if(!empty($r['vehicle_details'])): ?>
                                <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg border dark:border-slate-700 block text-center"><?php echo htmlspecialchars($r['vehicle_details']); ?></span>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-600 text-xs italic">No Vehicle</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest font-style-normal <?php echo $r['status']=='expired' ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400'; ?>"><?php echo $r['status'] == 'expired' ? 'Completed' : $r['status']; ?></span>
                        </td>
                        <td class="p-6 text-[10px] font-bold text-slate-400 dark:text-slate-500 italic uppercase font-style-normal">
                            <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                        </td>
                        <td class="p-6">
                            <?php if($r['actual_checkin']): ?>
                                <div class="space-y-1 font-bold text-[11px] font-style-normal">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        IN: <?php echo date('h:i:s A', strtotime($r['actual_checkin'])); ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-400 dark:text-slate-500">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                        OUT: <?php echo $r['actual_checkout'] ? date('h:i:s A', strtotime($r['actual_checkout'])) : 'N/A'; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-600 italic text-[11px] font-bold">No Entry</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6 text-right">
                            <?php if($r['actual_checkout']): ?>
                                <div class="inline-block px-4 py-2 bg-blue-50 dark:bg-blue-900/30 rounded-2xl border border-blue-100 dark:border-blue-900">
                                    <div class="font-black text-blue-600 dark:text-blue-400 italic text-base font-style-normal">
                                        <?php 
                                            $ts = $r['total_seconds'];
                                            $h = floor($ts / 3600); $m = floor(($ts % 3600) / 60); $s = $ts % 60;
                                            echo ($h > 0 ? "{$h}h " : "") . ($m > 0 ? "{$m}m " : "") . "{$s}s"; 
                                        ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-200 dark:text-slate-700 font-bold font-style-normal">—</span>
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