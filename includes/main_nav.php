<?php
$currentFile = basename($_SERVER['PHP_SELF']);
$isHome = ($currentFile === 'index.php');
?>
<style>
html { overflow-y: scroll; }
</style>
<nav class="w-full py-5 relative z-10">
    <div class="w-full max-w-5xl mx-auto px-6 flex justify-between items-center">

        <a href="index.php" class="text-xl font-black text-white tracking-tighter flex items-center gap-2.5 hover:opacity-80 transition-opacity">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30 flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            VISIT TRACK
        </a>

        <div class="flex items-center gap-2">
            <?php if (!$isHome): ?>
            <a href="index.php" class="flex items-center gap-2 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Home
            </a>
            <?php endif; ?>

            <a href="login.php" class="flex items-center gap-2 px-4 py-2.5 text-[11px] font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Admin
            </a>
        </div>

    </div>
</nav>
