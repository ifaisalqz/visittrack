<?php
// تحديد الصفحة الحالية لتفعيل اللون الأزرق
$currentPage = basename($_SERVER['PHP_SELF']);

// جلب اسم المستخدم من الجلسة (إذا كان محفوظاً)، وإلا نعرض "Admin" كافتراضي
$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>
<aside class="w-72 bg-slate-900 dark:bg-black text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl">
    
    <div class="mb-12">
        <h2 class="text-2xl font-black text-blue-400 tracking-tight font-style-normal">VISIT TRACK</h2>
        <p class="uppercase tracking-[0.3em] mt-1 text-slate-500 font-black text-[11px] font-style-normal">Admin Panel</p>
    </div>

    <nav class="space-y-3 flex-1 text-sm">
        <a href="index.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'index.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg font-bold font-style-normal italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal'; ?>">
            Live Requests
        </a>
        <a href="waiting_list.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'waiting_list.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg font-bold font-style-normal italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal'; ?>">
            Waiting & Active
        </a>
        <a href="logs.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'logs.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg font-bold font-style-normal italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold font-style-normal'; ?>">
            History Logs
        </a>
    </nav>

    <div class="space-y-3 mt-auto pt-8">
        
        <div class="flex items-center gap-3 p-3 bg-slate-500/5 rounded-2xl border border-slate-500/10 mb-4 font-style-normal backdrop-blur-sm">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md">
                <?php echo strtoupper(substr($adminName, 0, 1)); ?>
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Logged in as</p>
                <p class="text-sm font-black text-white truncate"><?php echo htmlspecialchars($adminName); ?></p>
            </div>
        </div>

        <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest text-left font-style-normal">
            🌓 Mode
        </button>
        <a href="logout.php" class="block w-full p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest font-style-normal">
            Logout
        </a>
    </div>
</aside>