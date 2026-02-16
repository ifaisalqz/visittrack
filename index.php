<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Track - Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { 
            darkMode: 'class' 
        }
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
        h1, h2, h3, label, button, input, textarea { font-style: normal !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors duration-300">

    <nav class="p-6 bg-white dark:bg-slate-900 sticky top-0 z-50 flex justify-between items-center px-12 border-b border-slate-100 dark:border-slate-800 shadow-sm">
        <h1 class="text-2xl font-black text-blue-600 tracking-tighter">VISIT TRACK</h1>
        
        <div class="flex items-center gap-6">
            <button onclick="toggleDarkMode()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-6 h-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <div class="hidden md:flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1 border border-slate-200 dark:border-slate-700">
                <input type="text" id="navTrackInput" placeholder="Track ID..." class="bg-transparent border-none outline-none px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 w-48 focus:ring-0">
                <button onclick="trackVisit('navTrackInput')" class="bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 px-4 py-2 rounded-lg font-black text-xs uppercase shadow-sm">Track</button>
            </div>
            <a href="login.php" class="px-5 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl font-bold text-sm">Admin</a>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto my-12 px-6">
        <div class="p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-slate-50 dark:border-slate-800">
            <div class="text-center mb-10">
                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest">Pre-Registration</span>
                <h2 class="text-4xl font-extrabold tracking-tight text-slate-800 dark:text-white mt-4">Visitor Registration</h2>
                <p class="text-slate-400 mt-2 font-medium">Please fill your details to receive your visitor pass</p>
            </div>
            
            <form action="submit_request.php" method="POST" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Full Name</label>
                        <input type="text" name="name" placeholder="Enter your full name" required class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Phone Number</label>
                        <input type="tel" name="phone" placeholder="05xxxxxxxx" required class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white">
                    </div>
                </div>

                <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-[2.5rem] border border-slate-100 dark:border-slate-700 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase ml-1">Arrival Time</label>
                            <input type="time" name="arrival" min="07:00" max="16:00" required class="w-full p-4 rounded-xl bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-2 ring-blue-500 font-black dark:text-white">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase ml-1">Departure Time</label>
                            <input type="time" name="departure" min="07:00" max="16:00" required class="w-full p-4 rounded-xl bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-2 ring-blue-500 font-black dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Purpose of Visit</label>
                    <textarea name="purpose" rows="3" placeholder="Describe the reason for your visit..." required class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white"></textarea>
                </div>

                <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl transition-all text-lg uppercase tracking-widest hover:bg-blue-700 active:scale-[0.98] shadow-[0_10px_30px_-10px_rgba(37,99,235,0.5)] dark:shadow-[0_15px_40px_-10px_rgba(37,99,235,0.7)]">
                    Register Visit
                </button>
            </form>
        </div>
    </div>

    <script>
        function trackVisit(inputId) {
            const tid = document.getElementById(inputId).value.trim();
            if (tid) window.location.href = 'visitor_status.php?tid=' + tid;
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            const tid = urlParams.get('tid');
            Swal.fire({
                title: 'Success!',
                html: `<div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-2xl my-4"><p class="text-xs font-black uppercase text-blue-400">Tracking ID</p><h2 class="text-3xl font-black text-blue-600">${tid}</h2></div>`,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }).then(() => { window.location.href = 'visitor_status.php?tid=' + tid; });
        }
    </script>
</body>
</html>