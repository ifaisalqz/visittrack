<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Track - Home</title>
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
        <h1 class="text-2xl font-black text-blue-600 tracking-tighter">VISIT TRACK</h1>
        <div class="flex items-center gap-6">
            <button onclick="toggleDarkMode()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-6 h-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <a href="login.php" class="px-5 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl font-bold text-sm">Admin</a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="max-w-4xl w-full text-center space-y-12">
            <div class="space-y-4">
                <h2 class="text-5xl md:text-6xl font-extrabold tracking-tight text-slate-800 dark:text-white">Welcome to <span class="text-blue-600">Visit Track</span></h2>
                <p class="text-lg text-slate-500 dark:text-slate-400 font-medium max-w-2xl mx-auto">Manage your visits seamlessly. Register for a new visit or track the status of an existing request.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
                <a href="register.php" class="group p-8 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl hover:shadow-2xl dark:shadow-[0_10px_30px_rgba(0,0,0,0.5)] border border-slate-50 dark:border-slate-800 transition-all hover:-translate-y-2 flex flex-col items-center justify-center space-y-4">
                    <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Register Visit</h3>
                    <p class="text-slate-400 text-sm font-medium">Apply for a new visitor pass</p>
                </a>

                <a href="track.php" class="group p-8 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl hover:shadow-2xl dark:shadow-[0_10px_30px_rgba(0,0,0,0.5)] border border-slate-50 dark:border-slate-800 transition-all hover:-translate-y-2 flex flex-col items-center justify-center space-y-4">
                    <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Track Status</h3>
                    <p class="text-slate-400 text-sm font-medium">Check your visit request status</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>