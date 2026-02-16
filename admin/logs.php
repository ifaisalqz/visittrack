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
    <title>Logs History - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
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
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-black, .font-bold, h1, h2, h3 { font-style: normal !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen">
    
    <aside class="w-72 bg-slate-900 dark:bg-black text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl">
        <div class="mb-12">
            <h2 class="text-2xl font-black text-blue-400 tracking-tight">VISIT TRACK</h2>
            <p class="uppercase tracking-[0.3em] mt-1 text-slate-500 font-black text-[13px]">Admin Panel</p>
        </div>
        <nav class="space-y-3 flex-1 text-sm">
            <a href="index.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold">Live Requests</a>
            <a href="logs.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg font-bold">History Logs</a>
            <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest text-left">🌓 Toggle Mode</button>
        </nav>
        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest">Logout</a>
    </aside>

    <main class="flex-1 p-12 text-slate-800 dark:text-white">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-black tracking-tight">System Logs</h1>
                <p class="text-slate-400 mt-2 font-medium">Archive of past visits</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Status</span>
                <span class="px-4 py-2 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full text-xs font-bold">Connected</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10 text-center">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                <h3 class="text-3xl font-black"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border-l-4 border-l-green-500 dark:border-slate-800">
                <p class="text-[10px] font-black text-green-600 uppercase tracking-widest">Completed</p>
                <h3 class="text-3xl font-black text-green-600"><?php echo (int)$stats['completed']; ?></h3>
            </div>
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border-l-4 border-l-red-500 dark:border-slate-800">
                <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">Rejected</p>
                <h3 class="text-3xl font-black text-red-600"><?php echo (int)$stats['rejected']; ?></h3>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6">Visitor Name</th>
                        <th class="p-6">Ticket ID</th>
                        <th class="p-6">Status</th>
                        <th class="p-6">Duration</th>
                        <th class="p-6 text-right">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($logs as $r): ?>
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-blue-900/10">
                        <td class="p-6 font-bold text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td class="p-6"><code class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-lg font-bold text-xs">#<?php echo $r['tracking_id']; ?></code></td>
                        <td class="p-6"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $r['status']=='expired' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>"><?php echo $r['status']; ?></span></td>
                        <td class="p-6 font-bold text-sm text-slate-600 dark:text-slate-400"><?php echo floor($r['total_seconds']/60); ?>m</td>
                        <td class="p-6 text-right font-bold text-slate-800 dark:text-slate-100"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>