<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

$now_dt = date('Y-m-d H:i:s');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'checkin') {
        $conn->prepare("UPDATE visitors SET actual_checkin = ? WHERE id = ?")->execute([$now_dt, $id]);
    } elseif ($_GET['action'] == 'checkout') {
        $conn->prepare("UPDATE visitors SET status = 'expired', actual_checkout = ? WHERE id = ?")->execute([$now_dt, $id]);
        header("Location: logs.php"); exit();
    }
    header("Location: waiting_list.php"); exit();
}

$waiting = $conn->query("SELECT * FROM visitors WHERE status = 'approved' AND actual_checkin IS NULL ORDER BY arrival_time ASC")->fetchAll();
$inside = $conn->query("SELECT * FROM visitors WHERE status = 'approved' AND actual_checkin IS NOT NULL AND actual_checkout IS NULL ORDER BY actual_checkin DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waiting & Active - Admin</title>
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
            <a href="waiting_list.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg font-bold font-style-normal italic">Waiting & Active</a>
            <a href="logs.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal">History Logs</a>
            <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest text-left font-style-normal">🌓 Mode</button>
        </nav>
        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest font-style-normal">Logout</a>
    </aside>

    <main class="flex-1 p-12 space-y-12 overflow-y-auto">
        <header class="flex justify-between items-end text-slate-800 dark:text-white">
            <h1 class="text-4xl font-black tracking-tight font-style-normal italic">Security Management</h1>
            <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Live Tracking</p>
        </header>

        <section>
            <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400 mb-6 italic uppercase font-style-normal">Approved (Awaiting Arrival)</h3>
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <?php foreach($waiting as $r): ?>
                        <tr>
                            <td class="p-6">
                                <div class="font-black text-slate-800 dark:text-slate-100 font-style-normal"><?php echo htmlspecialchars($r['full_name']); ?></div>
                                <div class="text-[9px] font-bold text-slate-400 italic opacity-60">PLAN: <?php echo date('h:i A', strtotime($r['arrival_time'])); ?> - <?php echo date('h:i A', strtotime($r['departure_time'])); ?></div>
                            </td>
                            <td class="p-6 text-right"><a href="?action=checkin&id=<?php echo $r['id']; ?>" class="px-6 py-3 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase font-style-normal tracking-widest shadow-lg">Check-in</a></td>
                        </tr>
                        <?php endforeach; if(empty($waiting)) echo "<tr><td class='p-10 text-center text-slate-400 italic'>No approved visitors currently waiting</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h3 class="text-2xl font-black text-green-600 mb-6 italic uppercase font-style-normal animate-pulse">Currently Inside</h3>
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-green-100 dark:border-slate-800 overflow-hidden">
                <table class="w-full text-left">
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <?php foreach($inside as $r): ?>
                        <tr>
                            <td class="p-6">
                                <div class="font-black text-slate-800 dark:text-slate-100 font-style-normal"><?php echo htmlspecialchars($r['full_name']); ?></div>
                                <div class="text-[9px] font-bold text-green-600 italic uppercase">Entered At: <?php echo date('h:i:s A', strtotime($r['actual_checkin'])); ?></div>
                            </td>
                            <td class="p-6 text-right"><a href="?action=checkout&id=<?php echo $r['id']; ?>" class="px-6 py-3 bg-orange-500 text-white rounded-xl text-[10px] font-black uppercase font-style-normal tracking-widest shadow-lg">Check-out</a></td>
                        </tr>
                        <?php endforeach; if(empty($inside)) echo "<tr><td class='p-10 text-center text-slate-400 italic'>No visitors currently inside the facility</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>