<!DOCTYPE html>
<html lang="en" class="dark" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Track - Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: "class" }</script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; }
        .font-style-normal { font-style: normal !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] min-h-screen transition-colors duration-300 relative flex flex-col selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/20 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

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

    <main class="flex-1 flex flex-col items-center justify-center p-6 w-full max-w-4xl mx-auto relative z-10 my-8">
        <div class="w-full bg-white dark:bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-800 p-8 md:p-12">
            
            <div class="text-center mb-10">
                <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-500/20">New Application</span>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800 dark:text-white mt-4 font-style-normal">Visitor Registration</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium">Please fill your details below to receive your entry pass.</p>
            </div>
            
            <form action="submit_request.php" method="POST" class="space-y-8">
                <div class="bg-slate-50 dark:bg-[#0B1120]/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700/50 pb-2 font-style-normal">1. Personal Info</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Full Name</label>
                            <input type="text" name="name" placeholder="Enter your full name" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">National ID / Iqama</label>
                            <input type="text" name="national_id" placeholder="10xxxxxxxx" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Phone Number</label>
                            <input type="tel" name="phone" placeholder="05xxxxxxxx" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Email Address</label>
                            <input type="email" name="email" placeholder="example@email.com" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-[#0B1120]/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700/50 pb-2 font-style-normal">2. Visit Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Host Name</label>
                            <input type="text" name="host_name" placeholder="Who are you visiting?" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Purpose of Visit</label>
                            <input type="text" name="purpose" placeholder="Reason for visit" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                    </div>
                    <!-- تاريخ الزيارة -->
                    <div class="space-y-2 mb-6">
                        <label class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest ml-1 font-style-normal">Visit Date</label>
                        <input type="date" name="visit_date" id="visitDate" required
                               class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 shadow-sm outline-none font-black dark:text-white text-sm">
                        <p class="text-[10px] font-bold text-slate-400 ml-1">Working days: Sunday to Thursday only</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest ml-1 font-style-normal">Arrival Time</label>
                            <input type="time" name="arrival" min="07:00" max="15:30" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 shadow-sm outline-none font-black dark:text-white text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest ml-1 font-style-normal">Departure Time</label>
                            <input type="time" name="departure" min="07:00" max="15:30" required class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 shadow-sm outline-none font-black dark:text-white text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-[#0B1120]/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6 border-b border-slate-200 dark:border-slate-700/50 pb-2 font-style-normal">3. Vehicle Access (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Car Model</label>
                            <input type="text" name="car_model" placeholder="e.g. 2024 Changan" class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 font-style-normal">Plate Number</label>
                            <input type="text" name="plate_number" placeholder="e.g. ABC 1234" class="w-full p-4 rounded-2xl bg-white dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none transition-all font-bold dark:text-white shadow-sm uppercase text-sm">
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl transition-all text-sm uppercase tracking-widest hover:bg-blue-700 active:scale-[0.98] shadow-lg shadow-blue-500/30 flex justify-center items-center gap-3 font-style-normal">
                    <span id="btnText">Register & Get Pass</span>
                    <svg id="spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </form>
        </div>
    </main>

    <script>
        // ── إعداد حقل التاريخ: الحد الأدنى اليوم، تعطيل عطل نهاية الأسبوع ──
        (function setupDateField() {
            const dateInput = document.getElementById('visitDate');
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];
            dateInput.min = todayStr;

            // اقترح أقرب يوم عمل (أحد-خميس)
            function nextWorkday(d) {
                const day = d.getDay(); // 0=Sun,1=Mon,...,5=Fri,6=Sat
                if (day === 5) d.setDate(d.getDate() + 2); // جمعة → أحد
                if (day === 6) d.setDate(d.getDate() + 1); // سبت → أحد
                return d;
            }
            const suggested = nextWorkday(new Date(today));
            dateInput.value = suggested.toISOString().split('T')[0];

            // عند تغيير التاريخ — تحقق إذا عطلة
            dateInput.addEventListener('change', function() {
                const d = new Date(this.value + 'T00:00:00');
                const day = d.getDay();
                if (day === 5 || day === 6) {
                    alert("Friday and Saturday are weekends. Please select Sunday to Thursday.")
                    // عد لأقرب يوم عمل
                    const next = nextWorkday(new Date(d));
                    this.value = next.toISOString().split('T')[0];
                }
            });
        })();

        // ── التحقق من ساعات الدوام ──
        const OPEN_H = 7, OPEN_M = 0;
        const CLOSE_H = 15, CLOSE_M = 30;

        function toMins(h, m) { return h * 60 + m; }
        function parseTime(val) {
            const [h, m] = val.split(':').map(Number);
            return toMins(h, m);
        }

        const OPEN  = toMins(OPEN_H, OPEN_M);
        const CLOSE = toMins(CLOSE_H, CLOSE_M);

        document.querySelector('form').addEventListener('submit', function(e) {
            const visitDate = document.getElementById('visitDate').value;
            const arrival   = document.querySelector('[name="arrival"]').value;
            const departure = document.querySelector('[name="departure"]').value;

            if (!visitDate || !arrival || !departure) return;

            // تحقق من يوم العمل
            const d = new Date(visitDate + 'T00:00:00');
            const day = d.getDay();
            if (day === 5 || day === 6) {
                e.preventDefault();
                alert("Friday and Saturday are weekends. Please select Sunday to Thursday.")
                return;
            }

            const arrMins = parseTime(arrival);
            const depMins = parseTime(departure);

            // تحقق من ساعات الدوام
            if (arrMins < OPEN || arrMins > CLOSE) {
                e.preventDefault();
                alert("Arrival time is outside working hours (7:00 AM - 3:30 PM).")
                return;
            }
            if (depMins < OPEN || depMins > CLOSE) {
                e.preventDefault();
                alert("Departure time is outside working hours (7:00 AM - 3:30 PM).")
                return;
            }
            if (depMins <= arrMins) {
                e.preventDefault();
                alert("Departure time must be after arrival time.")
                return;
            }

            // إذا اليوم ذا — الوقت ما يكون مضى
            const today = new Date().toISOString().split('T')[0];
            if (visitDate === today) {
                const nowMins = toMins(new Date().getHours(), new Date().getMinutes());
                if (nowMins > CLOSE) {
                    e.preventDefault();
                    alert("Company is closed today. Please select a future workday.")
                    return;
                }
                if (arrMins < nowMins) {
                    e.preventDefault();
                    alert("Arrival time has already passed. Choose a future time or book for another day.")
                    return;
                }
            }

            // نجح
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            document.getElementById('btnText').innerText = 'Processing Request...';
            document.getElementById('spinner').classList.remove('hidden');
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            const tid = urlParams.get('tid');
            Swal.fire({
                title: 'Registration Successful!',
                html: `<div class="p-4 bg-blue-50 dark:bg-blue-500/10 rounded-2xl my-4 text-center border border-blue-100 dark:border-blue-500/20">
                        <div id="swal-qr" class="flex justify-center mb-4"></div>
                        <p class="text-[10px] font-black uppercase text-blue-500 dark:text-blue-400 mb-1 tracking-widest">Tracking ID</p>
                        <h2 class="text-2xl font-black text-blue-600 dark:text-blue-400 tracking-widest font-mono">${tid}</h2>
                       </div>`,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#0f172a',
                didOpen: () => {
                    new QRCode(document.getElementById('swal-qr'), {
                        text: tid,
                        width: 150,
                        height: 150,
                        colorDark: '#0f172a',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            }).then(() => { window.location.href = 'visitor_status.php?tid=' + tid; });
        }
    </script>
</body>
</html>