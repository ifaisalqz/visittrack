<?php
// تحديد الصفحة الحالية لتفعيل اللون الأزرق
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-72 bg-slate-900 dark:bg-black text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl transition-colors duration-300 z-50 border-r border-slate-800 dark:border-slate-900">
    <div class="mb-12">
        <h2 class="text-2xl font-black text-blue-400 tracking-tight">VISIT TRACK</h2>
        <p class="uppercase tracking-[0.3em] mt-1 text-slate-500 font-black text-[10px]">Admin Panel</p>
    </div>
    <nav class="space-y-2 flex-1 text-sm">
        <a href="index.php" class="flex items-center gap-4 p-4 font-bold rounded-2xl transition-all duration-200 <?php echo ($currentPage == 'index.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
            Live Requests
        </a>
        <a href="waiting_list.php" class="flex items-center gap-4 p-4 font-bold rounded-2xl transition-all duration-200 <?php echo ($currentPage == 'waiting_list.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
            Waiting & Active
        </a>
        <a href="logs.php" class="flex items-center gap-4 p-4 font-bold rounded-2xl transition-all duration-200 <?php echo ($currentPage == 'logs.php') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?>">
            History Logs
        </a>
    </nav>
    <div class="space-y-3 mt-auto pt-8">
        <button onclick="toggleDarkMode()" class="w-full flex items-center justify-center gap-3 p-4 text-slate-400 hover:bg-slate-800 hover:text-white rounded-2xl transition-all duration-200 font-bold uppercase text-[10px] tracking-widest">
            🌓 Toggle Theme
        </button>
        <a href="logout.php" class="block w-full p-4 text-red-400 hover:bg-red-500 hover:text-white rounded-2xl transition-all duration-200 font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest">
            Logout
        </a>
    </div>
</aside>