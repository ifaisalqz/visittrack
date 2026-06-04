<!DOCTYPE html>
<html lang="en" class="dark" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Track — Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        html { overflow-y: scroll; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .step { display: none; }
        .step.active { display: block; }
    </style>
</head>
<body class="bg-[#0B1120] min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <?php include 'includes/main_nav.php'; ?>

    <main class="flex-1 w-full flex flex-col items-center justify-center py-8 px-6">
        <div class="w-full max-w-2xl mx-auto">

            <!-- Step indicator -->
            <div class="flex items-center justify-center gap-3 mb-8">
                <div id="dot1" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center transition-all">1</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-400 hidden sm:block">Personal</span>
                </div>
                <div class="w-8 h-px bg-slate-700"></div>
                <div id="dot2" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-xs font-black flex items-center justify-center transition-all">2</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 hidden sm:block">Visit</span>
                </div>
                <div class="w-8 h-px bg-slate-700"></div>
                <div id="dot3" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-xs font-black flex items-center justify-center transition-all">3</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 hidden sm:block">Vehicle</span>
                </div>
            </div>

            <form action="submit_request.php" method="POST">

                <!-- Step 1: Personal Info -->
                <div class="step active" id="step1">
                    <div class="bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-800 p-8">
                        <h2 class="text-2xl font-black text-white tracking-tighter mb-1">Personal Info</h2>
                        <p class="text-slate-400 text-sm mb-8">Tell us who you are.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full Name</label>
                                <input type="text" name="name" id="f_name" placeholder="Enter your full name" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">National ID / Iqama</label>
                                <input type="text" name="national_id" id="f_nid" placeholder="10xxxxxxxx" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Number</label>
                                <input type="tel" name="phone" id="f_phone" placeholder="05xxxxxxxx" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                                <input type="email" name="email" id="f_email" placeholder="example@email.com" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                        </div>
                        <button type="button" onclick="goTo(2)" class="mt-8 w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl uppercase tracking-widest text-xs transition-all active:scale-[0.98]">
                            Continue →
                        </button>
                    </div>
                </div>

                <!-- Step 2: Visit Details -->
                <div class="step" id="step2">
                    <div class="bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-800 p-8">
                        <h2 class="text-2xl font-black text-white tracking-tighter mb-1">Visit Details</h2>
                        <p class="text-slate-400 text-sm mb-8">Tell us about your visit.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Host Name</label>
                                <input type="text" name="host_name" id="f_host" placeholder="Who are you visiting?" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Purpose of Visit</label>
                                <input type="text" name="purpose" id="f_purpose" placeholder="Reason for visit" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Visit Date</label>
                                <input type="date" name="visit_date" id="visitDate" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-black text-sm">
                                <p class="text-[10px] font-bold text-slate-500">Working days: Sunday to Thursday only</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Arrival Time</label>
                                <input type="time" name="arrival" min="07:00" max="15:30" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-black text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Departure Time</label>
                                <input type="time" name="departure" min="07:00" max="15:30" required
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-black text-sm">
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" onclick="goTo(1)" class="w-1/3 py-4 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-2xl uppercase tracking-widest text-xs transition-all">
                                ← Back
                            </button>
                            <button type="button" onclick="goTo(3)" class="flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl uppercase tracking-widest text-xs transition-all active:scale-[0.98]">
                                Continue →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Vehicle -->
                <div class="step" id="step3">
                    <div class="bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-800 p-8">
                        <h2 class="text-2xl font-black text-white tracking-tighter mb-1">Vehicle Access</h2>
                        <p class="text-slate-400 text-sm mb-8">Optional — skip if you're not driving.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Car Model</label>
                                <input type="text" name="car_model" placeholder="e.g. 2024 Changan"
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Plate Number</label>
                                <input type="text" name="plate_number" placeholder="e.g. ABC 1234"
                                    class="w-full p-4 rounded-2xl bg-slate-800 border-2 border-transparent focus:border-blue-500 outline-none text-white font-bold text-sm uppercase">
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" onclick="goTo(2)" class="w-1/3 py-4 bg-slate-800 hover:bg-slate-700 text-white font-black rounded-2xl uppercase tracking-widest text-xs transition-all">
                                ← Back
                            </button>
                            <button type="submit" id="submitBtn" class="flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl uppercase tracking-widest text-xs transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                                <span id="btnText">Register & Get Pass</span>
                                <svg id="spinner" class="animate-spin h-4 w-4 text-white hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <script>
    function goTo(n) {
        // Validate step 1 before moving
        if (n === 2) {
            const fields = ['f_name','f_nid','f_phone','f_email'];
            for (const id of fields) {
                const el = document.getElementById(id);
                if (!el.value.trim()) { el.focus(); el.classList.add('border-red-500'); return; }
                el.classList.remove('border-red-500');
            }
        }
        // Validate step 2 before moving
        if (n === 3) {
            const host    = document.getElementById('f_host');
            const purpose = document.getElementById('f_purpose');
            const date    = document.getElementById('visitDate');
            const arrival = document.querySelector('[name="arrival"]');
            const depart  = document.querySelector('[name="departure"]');
            for (const el of [host, purpose, date, arrival, depart]) {
                if (!el.value.trim()) { el.focus(); el.classList.add('border-red-500'); return; }
                el.classList.remove('border-red-500');
            }
            // Weekday check
            const d = new Date(date.value + 'T00:00:00');
            if (d.getDay() === 5 || d.getDay() === 6) {
                alert('Friday and Saturday are weekends. Please select Sunday to Thursday.');
                date.focus(); return;
            }
            // Time checks
            const toMins = v => parseInt(v)*60 + parseInt(v.split(':')[1]);
            const OPEN = 7*60, CLOSE = 15*60+30;
            if (toMins(arrival.value) < OPEN || toMins(arrival.value) > CLOSE) { alert('Arrival time is outside working hours (7:00 AM - 3:30 PM).'); arrival.focus(); return; }
            if (toMins(depart.value)  < OPEN || toMins(depart.value)  > CLOSE) { alert('Departure time is outside working hours (7:00 AM - 3:30 PM).'); depart.focus(); return; }
            if (toMins(depart.value) <= toMins(arrival.value)) { alert('Departure time must be after arrival time.'); depart.focus(); return; }
            const today = new Date().toISOString().split('T')[0];
            if (date.value === today) {
                const nowMins = new Date().getHours()*60 + new Date().getMinutes();
                if (nowMins > CLOSE) { alert('Company is closed today. Please select a future workday.'); date.focus(); return; }
                if (toMins(arrival.value) < nowMins) { alert('Arrival time has already passed. Choose a future time or book for another day.'); arrival.focus(); return; }
            }
        }

        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + n).classList.add('active');

        // Update dots
        for (let i = 1; i <= 3; i++) {
            const dot   = document.querySelector('#dot' + i + ' div');
            const label = document.querySelector('#dot' + i + ' span');
            if (i < n) {
                dot.className   = 'w-8 h-8 rounded-full bg-green-500 text-white text-xs font-black flex items-center justify-center transition-all';
                dot.innerHTML   = '✓';
                if (label) label.className = 'text-[10px] font-black uppercase tracking-widest text-green-400 hidden sm:block';
            } else if (i === n) {
                dot.className   = 'w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center transition-all';
                dot.innerHTML   = i;
                if (label) label.className = 'text-[10px] font-black uppercase tracking-widest text-blue-400 hidden sm:block';
            } else {
                dot.className   = 'w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-xs font-black flex items-center justify-center transition-all';
                dot.innerHTML   = i;
                if (label) label.className = 'text-[10px] font-black uppercase tracking-widest text-slate-500 hidden sm:block';
            }
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Date field setup
    (function() {
        const dateInput = document.getElementById('visitDate');
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        dateInput.min = todayStr;
        function nextWorkday(d) {
            const day = d.getDay();
            if (day === 5) d.setDate(d.getDate() + 2);
            if (day === 6) d.setDate(d.getDate() + 1);
            return d;
        }
        dateInput.value = nextWorkday(new Date(today)).toISOString().split('T')[0];
        dateInput.addEventListener('change', function() {
            const d = new Date(this.value + 'T00:00:00');
            if (d.getDay() === 5 || d.getDay() === 6) {
                alert('Friday and Saturday are weekends. Please select Sunday to Thursday.');
                this.value = nextWorkday(new Date(d)).toISOString().split('T')[0];
            }
        });
    })();

    // Submit handler
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        document.getElementById('btnText').innerText = 'Processing...';
        document.getElementById('spinner').classList.remove('hidden');
    });

    // Success popup
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const tid = urlParams.get('tid');
        Swal.fire({
            title: 'Registration Successful!',
            html: `<div class="p-4 bg-blue-500/10 rounded-2xl my-4 text-center border border-blue-500/20">
                    <div id="swal-qr" class="flex justify-center mb-4"></div>
                    <p class="text-[10px] font-black uppercase text-blue-400 mb-1 tracking-widest">Tracking ID</p>
                    <h2 class="text-2xl font-black text-blue-400 tracking-widest font-mono">${tid}</h2>
                   </div>`,
            icon: 'success',
            confirmButtonColor: '#2563eb',
            background: '#0f172a',
            color: '#fff',
            didOpen: () => {
                new QRCode(document.getElementById('swal-qr'), {
                    text: tid, width: 150, height: 150,
                    colorDark: '#0f172a', colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        }).then(() => { window.location.href = 'visitor_status.php?tid=' + tid; });
    }
    </script>
    <?php include 'includes/main_footer.php'; ?>
</body>
</html>
