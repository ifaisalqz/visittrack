<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Visit Track</title>
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

    <!-- ناف بار نظيف جداً ومختصر -->
    <nav class="w-full max-w-7xl mx-auto p-6 md:px-12 flex justify-between items-center relative z-10">
        <div class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter font-style-normal flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            VISIT TRACK
        </div>
        
        <div class="flex items-center gap-2 md:gap-4">
            <button onclick="toggleDarkMode()" class="p-3 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-xl transition-all">
                <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <a href="login.php" class="px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-style-normal">
                Admin
            </a>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 w-full max-w-5xl mx-auto relative z-10">
        
        <!-- تأثير إضاءة خفيف في الخلفية للـ Dark Mode -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/20 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

        <div class="text-center space-y-6 mb-16 w-full">
            <h1 class="text-5xl md:text-7xl font-black tracking-tighter leading-[1.1] text-slate-800 dark:text-white font-style-normal">
                Smart & Secure <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">Visitor Management</span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg font-medium max-w-xl mx-auto leading-relaxed">
                Experience seamless facility access. Request your visit, get approved, and use your digital QR pass for instant check-in.
            </p>
        </div>

        <!-- بطاقات الخيارات بدل الأزرار العادية -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-3xl">
            
            <!-- بطاقة التسجيل -->
            <a href="register.php" class="group relative bg-white dark:bg-slate-900/80 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300 hover:-translate-y-2 overflow-hidden text-left font-style-normal block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mb-6 relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                
                <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2 relative z-10">New Visit Request</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-8 relative z-10">Pre-register your visit details to receive an entry QR code.</p>
                
                <div class="flex items-center text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 relative z-10 group-hover:translate-x-2 transition-transform">
                    Register Now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </a>

            <!-- بطاقة التتبع -->
            <a href="track.php" class="group relative bg-white dark:bg-slate-900/80 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none hover:border-slate-400 dark:hover:border-slate-600 transition-all duration-300 hover:-translate-y-2 overflow-hidden text-left font-style-normal block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-500/5 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                
                <div class="w-14 h-14 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-6 relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                
                <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2 relative z-10">Track Status</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-8 relative z-10">Check if your request is approved or rejected by the host.</p>
                
                <div class="flex items-center text-xs font-black uppercase tracking-widest text-slate-800 dark:text-white relative z-10 group-hover:translate-x-2 transition-transform">
                    Check Status
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </a>

        </div>
    </main>

    <footer class="p-6 text-center text-[10px] font-black uppercase tracking-widest text-slate-400 font-style-normal">
        &copy; <?php echo date('Y'); ?> Visit Track. All rights reserved.
    </footer>

</body>
</html>