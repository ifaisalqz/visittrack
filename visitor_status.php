<?php
include 'includes/db.php';
$tid = $_GET['tid'] ?? '';
$stmt = $conn->prepare("SELECT * FROM visitors WHERE tracking_id = ?");
$stmt->execute([$tid]);
$v = $stmt->fetch();
if (!$v) { header("Location: index.php?error=notfound"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status - Visit Track</title>
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
        h2, p, span, button, div { font-style: normal !important; }
        .status-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <a href="index.php" class="text-slate-400 font-bold text-sm hover:text-blue-600 transition-all">Back to Registration</a>
            <button onclick="toggleDarkMode()" class="text-xs font-black uppercase text-slate-400">🌓 Mode</button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-800 relative">
            <div class="bg-slate-900 dark:bg-black p-8 text-center relative transition-colors">
                <div class="absolute top-0 left-0 w-full h-1 bg-blue-600"></div>
                <h2 class="text-white text-2xl font-black tracking-tight">Visitor Pass</h2>
                <div class="mt-2 inline-block px-4 py-1 bg-slate-800 dark:bg-slate-900 rounded-full border border-slate-700">
                    <span class="text-blue-400 font-mono font-bold text-sm tracking-widest uppercase">#<?php echo $tid; ?></span>
                </div>
            </div>

            <div class="p-10 text-center">
                <div class="mb-8">
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest mb-1">Visitor Name</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight"><?php echo htmlspecialchars($v['full_name']); ?></h3>
                </div>

                <div class="p-8 rounded-[2.5rem] mb-8 border-2 <?php 
                    if ($v['status'] == 'approved') echo 'bg-green-50 border-green-100 text-green-600 dark:bg-green-900/20';
                    elseif ($v['status'] == 'pending') echo 'bg-blue-50 border-blue-100 text-blue-600 status-pulse dark:bg-blue-900/20';
                    elseif ($v['status'] == 'rejected') echo 'bg-red-50 border-red-100 text-red-600 dark:bg-red-900/20';
                    else echo 'bg-slate-50 border-slate-100 text-slate-400 dark:bg-slate-800';
                ?>">
                    <span class="text-[10px] uppercase font-black tracking-widest block mb-2 opacity-70">Status</span>
                    <p class="text-4xl font-black uppercase tracking-tighter"><?php echo ($v['status'] == 'approved') ? 'Checked-In' : $v['status']; ?></p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                        <span class="text-slate-400 block text-[9px] font-black uppercase mb-1">Arrival</span>
                        <span class="font-bold text-sm"><?php echo date('h:i A', strtotime($v['arrival_time'])); ?></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                        <span class="text-slate-400 block text-[9px] font-black uppercase mb-1">Departure</span>
                        <span class="font-bold text-sm"><?php echo date('h:i A', strtotime($v['departure_time'])); ?></span>
                    </div>
                </div>

                <button onclick="location.reload()" class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition active:scale-95 shadow-lg shadow-blue-100 dark:shadow-none">Refresh Status</button>
            </div>
        </div>
    </div>
    <script><?php if($v['status'] == 'pending'): ?>setTimeout(() => { location.reload(); }, 10000);<?php endif; ?></script>
</body>
</html>