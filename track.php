<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Request - Visit Track</title>
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
        .font-style-normal { font-style: normal !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] min-h-screen flex flex-col transition-colors duration-300 selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/20 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <nav class="w-full max-w-7xl mx-auto p-6 md:px-12 flex justify-between items-center relative z-10">
        <a href="index.php" class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter font-style-normal flex items-center gap-2 hover:opacity-80 transition-opacity">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            VISIT TRACK
        </a>
        <div class="flex items-center gap-4">
            <button onclick="toggleDarkMode()" class="p-3 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-xl transition-all">
                <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <a href="index.php" class="px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-style-normal">Home</a>
        </div>
    </nav>

    <main class="flex-1 flex items-center justify-center p-6 relative z-10">
        <div class="max-w-md w-full text-center space-y-8 animate-fade-in-up">
            
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white dark:bg-slate-900/80 backdrop-blur-xl rounded-[2rem] mb-2 shadow-sm border border-slate-100 dark:border-slate-800">
                <svg class="w-8 h-8 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            
            <div>
                <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter font-style-normal mb-3">Track Status</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed">Enter your Tracking ID to view your digital pass.</p>
            </div>
            
            <form action="visitor_status.php" method="GET" class="relative group mt-8">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </div>
                <input type="text" name="tid" required autocomplete="off" placeholder="VST-XXXXX" 
                class="block w-full pl-14 pr-32 py-5 bg-white dark:bg-slate-900/80 backdrop-blur-xl border-2 border-slate-100 dark:border-slate-800 rounded-[2rem] leading-5 outline-none transition-all duration-300 focus:border-blue-500 dark:focus:border-blue-500 shadow-xl shadow-slate-200/50 dark:shadow-none text-slate-800 dark:text-white font-black uppercase tracking-widest text-sm placeholder:normal-case placeholder:font-medium placeholder:tracking-normal">
                
                <div class="absolute inset-y-2 right-2">
                    <button type="submit" class="h-full px-6 bg-slate-800 dark:bg-blue-600 hover:bg-slate-900 dark:hover:bg-blue-500 text-white font-black rounded-2xl shadow-md transition-all active:scale-[0.95] text-xs uppercase tracking-widest font-style-normal">
                        Track
                    </button>
                </div>
            </form>
            
        </div>
    </main>
</body>
</html>