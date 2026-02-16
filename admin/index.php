<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

// (PHP logic remains the same)

$active = $conn->query("SELECT * FROM visitors WHERE status IN ('pending', 'approved') ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Management - Visit Track</title>
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
            <a href="index.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg font-bold">Live Requests</a>
            <a href="logs.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold">History Logs</a>
            <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest">🌓 Toggle Mode</button>
        </nav>
        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest">Logout</a>
    </aside>

    <main class="flex-1 p-12">
        <header class="mb-12 flex justify-between items-center text-slate-800 dark:text-white">
            <div>
                <h1 class="text-4xl font-black tracking-tight">Active Management</h1>
                <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em]">Real-time visitor monitoring</p>
            </div>
            <button onclick="toggleModal()" class="px-6 py-4 bg-slate-800 dark:bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-blue-600 transition-all">+ Add Visitor</button>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6">Visitor</th>
                        <th class="p-6">Planned Time</th>
                        <th class="p-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($active as $r): ?>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-lg uppercase tracking-tighter"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-black text-blue-600 dark:text-blue-400 uppercase"><?php echo date('h:i A', strtotime($r['arrival_time'])); ?> - <?php echo date('h:i A', strtotime($r['departure_time'])); ?></div>
                            <div class="text-[9px] font-bold text-slate-300 dark:text-slate-500 mt-1 uppercase">Status: <?php echo $r['status']; ?></div>
                        </td>
                        <td class="p-6 flex justify-center gap-3">
                            <?php if($r['status'] == 'pending'): ?>
                                <a href="?action=checkin&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase shadow-lg">Confirm</a>
                                <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-red-500 rounded-xl text-[10px] font-black uppercase">Reject</a>
                            <?php elseif($r['status'] == 'approved'): ?>
                                <a href="?action=checkout&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-orange-500 text-white rounded-xl text-[10px] font-black uppercase shadow-lg">Departure</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 transition-all">
        <div class="modal-overlay absolute w-full h-full bg-slate-900 opacity-50"></div>
        <div class="modal-container bg-white dark:bg-slate-900 w-full max-w-lg mx-auto rounded-[2.5rem] shadow-2xl z-50 border border-slate-100 dark:border-slate-800 p-10">
            <h3 class="text-2xl font-black uppercase text-slate-800 dark:text-white mb-6">Add Walk-in</h3>
            <form action="index.php" method="POST" class="space-y-4">
                <input type="hidden" name="add_visitor" value="1">
                <input type="text" name="name" placeholder="Full Name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none">
                <div class="grid grid-cols-2 gap-4">
                    <input type="time" name="arrival" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none">
                    <input type="time" name="departure" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none">
                </div>
                <textarea name="purpose" placeholder="Purpose" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none"></textarea>
                <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl shadow-xl uppercase tracking-widest">Save</button>
            </form>
            <button onclick="toggleModal()" class="mt-4 text-slate-400 w-full font-bold">Close</button>
        </div>
    </div>
    <script>
        function toggleModal() {
            const m = document.getElementById('modal');
            m.classList.toggle('opacity-0'); m.classList.toggle('pointer-events-none');
        }
    </script>
</body>
</html>