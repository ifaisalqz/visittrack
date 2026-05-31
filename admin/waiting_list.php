<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
include '../includes/db.php';

if (isset($_GET['op']) && isset($_GET['id'])) {
    if ($_GET['op'] == 'checkin') {
        // ينتقل من approved إلى active فقط — يمنع تسجيل دخول مزدوج
        $conn->prepare("UPDATE visitors SET actual_checkin = NOW(), status = 'active' WHERE id = ? AND status = 'approved'")
             ->execute([$_GET['id']]);
    } elseif ($_GET['op'] == 'checkout') {
        // ينتقل من active إلى completed فقط — يمنع تسجيل خروج لمن لم يدخل
        $conn->prepare("UPDATE visitors SET actual_checkout = NOW(), status = 'completed' WHERE id = ? AND status = 'active'")
             ->execute([$_GET['id']]);
    }
    header("Location: waiting_list.php"); 
    exit();
}

$waiting = $conn->query("SELECT * FROM visitors WHERE status = 'approved' AND actual_checkin IS NULL ORDER BY arrival_time ASC")->fetchAll();
$active  = $conn->query("SELECT * FROM visitors WHERE status = 'active' AND actual_checkout IS NULL ORDER BY actual_checkin DESC")->fetchAll();
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
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }</style>
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
            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl overflow-hidden flex flex-col">
                <div class="p-8 border-b border-slate-200 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/20 flex items-center justify-between">
                    <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-widest">Expected Visitors</h2>
                    <span class="px-3 py-1 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-500/20 rounded-full text-[10px] font-black"><?php echo count($waiting); ?> Waiting</span>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/30 border-b border-slate-200 dark:border-slate-700/50"><tr class="uppercase text-[9px] font-black tracking-widest text-slate-500"><th class="p-6">Visitor Info</th><th class="p-6 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            <?php foreach($waiting as $w): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-6"><div class="font-black text-slate-800 dark:text-white text-base"><?php echo htmlspecialchars($w['full_name']); ?></div><div class="text-[10px] font-bold text-slate-500 mt-1">Expected: <?php echo date('h:i A', strtotime($w['arrival_time'])); ?></div></td>
                                <td class="p-6 text-right"><a href="?op=checkin&id=<?php echo $w['id']; ?>" class="inline-block px-5 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 active:scale-95 transition-all">Check-in</a></td>
                            </tr>
                            <?php endforeach; if(count($waiting) == 0): ?><tr><td colspan="2" class="p-8 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">No visitors waiting</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-blue-100 dark:border-blue-900/30 shadow-xl overflow-hidden flex flex-col relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 blur-[50px] pointer-events-none"></div>
                <div class="p-8 border-b border-slate-200 dark:border-slate-800/50 bg-slate-50 dark:bg-slate-800/20 flex items-center justify-between relative z-10">
                    <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-widest">Currently Inside</h2>
                    <span class="px-3 py-1 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-500/20 rounded-full text-[10px] font-black animate-pulse"><?php echo count($active); ?> Active</span>
                </div>
                <div class="overflow-x-auto flex-1 relative z-10">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/30 border-b border-slate-200 dark:border-slate-700/50"><tr class="uppercase text-[9px] font-black tracking-widest text-slate-500"><th class="p-6">Visitor Info</th><th class="p-6 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                            <?php foreach($active as $a): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="p-6"><div class="font-black text-slate-800 dark:text-white text-base"><?php echo htmlspecialchars($a['full_name']); ?></div><div class="text-[10px] font-bold text-green-600 dark:text-green-400 mt-1">Entered: <?php echo date('h:i A', strtotime($a['actual_checkin'])); ?></div></td>
                                <td class="p-6 text-right"><a href="?op=checkout&id=<?php echo $a['id']; ?>" class="inline-block px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-700 hover:border-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest active:scale-95 transition-all">Check-out</a></td>
                            </tr>
                            <?php endforeach; if(count($active) == 0): ?><tr><td colspan="2" class="p-8 text-center text-slate-500 text-xs font-bold uppercase tracking-widest">Facility is empty</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
