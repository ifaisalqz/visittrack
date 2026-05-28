<?php

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-72 bg-slate-900 dark:bg-black text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl">
    <div class="mb-12">
        <h2 class="text-2xl font-black text-blue-400 tracking-tight font-style-normal">VISIT TRACK</h2>
        <p class="uppercase tracking-[0.3em] mt-1 text-slate-500 font-black text-[11px] font-style-normal">Admin Panel</p>
    </div>
    <nav class="space-y-3 flex-1 text-sm">
        <a href="index.php" class="flex items-center gap-4 p-4 font-bold font-style-normal <?php echo ($currentPage == 'index.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition'; ?>">
            Live Requests
        </a>
        <a href="waiting_list.php" class="flex items-center gap-4 p-4 font-bold font-style-normal <?php echo ($currentPage == 'waiting_list.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition'; ?>">
            Waiting & Active
        </a>
        <a href="logs.php" class="flex items-center gap-4 p-4 font-bold font-style-normal <?php echo ($currentPage == 'logs.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg italic' : 'text-slate-400 hover:bg-slate-800 rounded-2xl transition'; ?>">
            History Logs
        </a>
        <button onclick="toggleDarkMode()" class="w-full flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 rounded-2xl transition font-bold uppercase text-[10px] tracking-widest text-left font-style-normal">
            🌓 Mode
        </button>
    </nav>
    <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest font-style-normal">
        Logout
    </a>
</aside>