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
        h1, h2, h3, label, button, input, textarea { font-style: normal !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors duration-300">

<nav class="p-6 bg-white dark:bg-slate-900 sticky top-0 z-50 flex justify-between items-center px-12 border-b border-slate-100 dark:border-slate-800 shadow-sm">
        <a href="index.php" class="text-2xl font-black text-blue-600 tracking-tighter hover:opacity-80 transition-opacity">VISIT TRACK</a>
        
        <div class="flex items-center gap-4 md:gap-6">
            <button onclick="toggleDarkMode()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-6 h-6 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <a href="index.php" class="text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 font-bold text-sm transition-colors">Home</a>
            <a href="track.php" class="hidden md:flex items-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2 rounded-xl font-bold text-sm border border-slate-200 dark:border-slate-700 transition-colors">Track Status</a>
            <a href="login.php" class="px-5 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl font-bold text-sm transition-colors">Admin</a>
        </div>
</nav>

    <div class="max-w-4xl mx-auto my-12 px-6">
        <div class="p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-slate-50 dark:border-slate-800">
            <div class="text-center mb-10">
                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest">Pre-Registration</span>
                <h2 class="text-4xl font-extrabold tracking-tight text-slate-800 dark:text-white mt-4">Visitor Registration</h2>
                <p class="text-slate-400 mt-2 font-medium">Please fill your details to receive your visitor pass</p>
            </div>
            
            <form action="submit_request.php" method="POST" class="space-y-8">
                <div class="bg-slate-50 dark:bg-slate-800/30 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700 pb-2">1. Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Full Name</label>
                            <input type="text" name="name" placeholder="Enter your full name" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">National ID / Iqama</label>
                            <input type="text" name="national_id" placeholder="10xxxxxxxx" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Phone Number</label>
                            <input type="tel" name="phone" placeholder="05xxxxxxxx" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Email Address</label>
                            <input type="email" name="email" placeholder="example@email.com" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/30 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700 pb-2">2. Visit Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Host Name</label>
                            <input type="text" name="host_name" placeholder="Employee name" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Purpose of Visit</label>
                            <input type="text" name="purpose" placeholder="Reason for visit" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase ml-1">Arrival Time</label>
                            <input type="time" name="arrival" min="07:00" max="16:00" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-2 ring-blue-500 font-black dark:text-white">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 uppercase ml-1">Departure Time</label>
                            <input type="time" name="departure" min="07:00" max="16:00" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-2 ring-blue-500 font-black dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/30 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700 pb-2">3. Vehicle Access (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Car Make & Model</label>
                            <input type="text" name="car_model" placeholder="e.g. 2024 Changan Eado Plus" class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-slate-400 uppercase ml-1 tracking-wider">Plate Number</label>
                            <input type="text" name="plate_number" placeholder="e.g. ABC 1234" class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm uppercase">
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl transition-all text-lg uppercase tracking-widest hover:bg-blue-700 active:scale-[0.98] shadow-[0_10px_30px_-10px_rgba(37,99,235,0.5)] dark:shadow-[0_15px_40px_-10px_rgba(37,99,235,0.7)] flex justify-center items-center gap-3">
                    <span id="btnText">Register Visit & Get QR Pass</span>
                    <svg id="spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            text.innerText = 'Processing Request...';
            spinner.classList.remove('hidden');
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            const tid = urlParams.get('tid');
            Swal.fire({
                title: 'Registration Successful!',
                html: `<div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-2xl my-4 text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${tid}" class="mx-auto rounded-lg mb-3 shadow-md" alt="QR Code">
                        <p class="text-xs font-black uppercase text-blue-400 mb-1">Tracking ID</p>
                        <h2 class="text-2xl font-black text-blue-600 tracking-widest">${tid}</h2>
                       </div>`,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }).then(() => { window.location.href = 'visitor_status.php?tid=' + tid; });
        }
    </script>
</body>
</html>