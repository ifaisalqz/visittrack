<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Track - Track Status</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors duration-300 flex flex-col">

<nav class="p-6 bg-white dark:bg-slate-900 sticky top-0 z-50 flex justify-between items-center px-12 border-b border-slate-100 dark:border-slate-800 shadow-sm">
        <a href="index.php" class="text-2xl font-black text-blue-600 tracking-tighter hover:opacity-80 transition-opacity">VISIT TRACK</a>
        
        <div class="flex items-center gap-4 md:gap-6">
            <button onclick="toggleDarkMode()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-6 h-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            
            <a href="index.php" class="hidden md:flex items-center text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 font-bold text-sm transition-colors">
                Home
            </a>
            
            <a href="register.php" class="hidden md:flex items-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2 rounded-xl font-bold text-sm border border-slate-200 dark:border-slate-700 transition-colors">
                New Registration
            </a>
            
            <a href="login.php" class="px-5 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl font-bold text-sm transition-colors">
                Admin
            </a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="max-w-xl w-full">
            <div class="p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-slate-50 dark:border-slate-800 text-center">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400 dark:text-slate-500">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-800 dark:text-white mb-2">Track Your Visit</h2>
                <p class="text-slate-400 font-medium mb-8">Enter your Tracking ID below to check your status.</p>

                <form onsubmit="event.preventDefault(); trackVisit();" class="space-y-4">
                    <input type="text" id="trackIdInput" placeholder="e.g. VT-12345" required class="w-full p-5 rounded-2xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold text-center text-xl dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-600">
                    
                    <button type="submit" class="w-full py-5 bg-slate-800 dark:bg-slate-700 text-white font-black rounded-2xl transition-all text-lg uppercase tracking-widest hover:bg-slate-900 dark:hover:bg-slate-600 active:scale-[0.98]">
                        Check Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function trackVisit() {
            const tid = document.getElementById('trackIdInput').value.trim();
            if (tid) {
                window.location.href = 'visitor_status.php?tid=' + encodeURIComponent(tid);
            }
        }
    </script>
</body>
</html>