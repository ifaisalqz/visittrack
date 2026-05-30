<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>
<aside class="w-72 bg-white/80 dark:bg-[#0B1120]/80 backdrop-blur-3xl border-r border-slate-200 dark:border-slate-800/80 p-8 sticky top-0 h-screen flex flex-col shadow-2xl transition-colors duration-300 z-50">
    
    <div class="mb-12">
        <h2 class="text-2xl font-black text-blue-600 dark:text-blue-500 tracking-tighter font-style-normal flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            VISIT TRACK
        </h2>
        <p class="uppercase tracking-[0.3em] mt-2 text-slate-400 dark:text-slate-500 font-black text-[9px] font-style-normal ml-10">Admin Panel</p>
    </div>

    <nav class="space-y-3 flex-1 text-sm">
        <a href="index.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'index.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/30 font-black font-style-normal' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 rounded-2xl transition-all font-bold font-style-normal hover:translate-x-1'; ?>">
            Live Requests
        </a>
        <a href="waiting_list.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'waiting_list.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/30 font-black font-style-normal' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 rounded-2xl transition-all font-bold font-style-normal hover:translate-x-1'; ?>">
            Waiting & Active
        </a>
        <a href="logs.php" class="flex items-center gap-4 p-4 <?php echo ($currentPage == 'logs.php') ? 'bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/30 font-black font-style-normal' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 rounded-2xl transition-all font-bold font-style-normal hover:translate-x-1'; ?>">
            History Logs
        </a>
    </nav>

    <div class="space-y-3 mt-auto pt-8 border-t border-slate-200 dark:border-slate-800/50">
        
        <div class="flex items-center gap-3 p-3 bg-slate-100 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/50 mb-4 font-style-normal">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md">
                <?php echo strtoupper(substr($adminName, 0, 1)); ?>
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Logged in as</p>
                <p class="text-sm font-black text-slate-800 dark:text-white truncate"><?php echo htmlspecialchars($adminName); ?></p>
            </div>
        </div>

        <button onclick="toggleDarkMode()" class="w-full flex items-center justify-between p-4 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/50 hover:text-slate-800 dark:hover:text-white rounded-2xl transition-all font-black uppercase text-[10px] tracking-widest font-style-normal">
            <span class="flex items-center gap-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                Toggle Theme
            </span>
        </button>
        <a href="../admin/logout.php" class="flex items-center justify-center w-full p-4 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-2xl transition-all font-black uppercase text-[10px] border border-red-100 dark:border-red-500/20 tracking-widest font-style-normal">
            Logout
        </a>
    </div>
</aside>