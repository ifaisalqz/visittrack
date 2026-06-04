<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php"); exit();
}
include '../includes/db.php';
include '../includes/mailer.php';

$isAdmin = ($_SESSION['role'] ?? 'supervisor') === 'admin';

function sendVisitorEmail($toEmail, $toName, $tid, $status, $arrival = null, $departure = null) {
    $type  = ($status === 'approved') ? 'approved' : 'rejected';
    $email = buildEmailHtml($type, ['name' => $toName, 'tid' => $tid, 'arrival' => $arrival, 'departure' => $departure]);
    return sendVisitEmail($toEmail, $toName, $email['subject'], $email['html']);
}

if ($isAdmin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_visitor'])) {
    $visit_date = $_POST['visit_date'] ?? date('Y-m-d');
    $phpDay = (int)date('w', strtotime($visit_date));
    if ($phpDay === 5 || $phpDay === 6) {
        $_SESSION['walkin_error'] = 'Friday and Saturday are weekends. Please select Sunday to Thursday.';
        header("Location: index.php"); exit();
    }
    $toMins = fn($t) => (int)explode(':',$t)[0]*60 + (int)explode(':',$t)[1];
    $OPEN = 7*60; $CLOSE = 15*60+30;
    $walkinError = null;
    if ($toMins($_POST['arrival']) < $OPEN || $toMins($_POST['arrival']) > $CLOSE) {
        $walkinError = 'Arrival time is outside working hours (7:00 AM — 3:30 PM).';
    } elseif ($toMins($_POST['departure']) < $OPEN || $toMins($_POST['departure']) > $CLOSE) {
        $walkinError = 'Departure time is outside working hours (7:00 AM — 3:30 PM).';
    }
    if ($walkinError) { $_SESSION['walkin_error'] = $walkinError; header("Location: index.php"); exit(); }
    $tid = "ADM-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $vehicle_details = trim($_POST['car_model'] . ' ' . $_POST['plate_number']);
    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, purpose, vehicle_details, arrival_time, departure_time, visit_date, tracking_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->execute([$_POST['name'], $_POST['phone'], $_POST['host_name'], $_POST['national_id'], $_POST['email'], $_POST['purpose'], $vehicle_details, $_POST['arrival'], $_POST['departure'], $visit_date, $tid]);
    @sendVisitorEmail($_POST['email'], $_POST['name'], $tid, 'approved', $_POST['arrival'], $_POST['departure']);
    header("Location: waiting_list.php"); exit();
}

if ($isAdmin && isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("SELECT email, full_name, tracking_id, arrival_time, departure_time FROM visitors WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $v = $stmt->fetch();
    if ($v) {
        $conn->prepare("UPDATE visitors SET status = ? WHERE id = ?")->execute([$status, $_GET['id']]);
        @sendVisitorEmail($v['email'], $v['full_name'], $v['tracking_id'], $status, $v['arrival_time'], $v['departure_time']);
    }
    header("Location: index.php"); exit();
}

$conn->exec("UPDATE visitors SET status='expired' WHERE status IN ('pending','approved') AND visit_date < CURDATE()");
$active      = $conn->query("SELECT * FROM visitors WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$walkinError = $_SESSION['walkin_error'] ?? null;
unset($_SESSION['walkin_error']);
?>
<!DOCTYPE html>
<html lang="en" class="dark" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Requests — VisitTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .arrow { transition: transform 0.2s ease; }</style>
    <script>
    function autoFillVisitor() {
        document.querySelector('input[name="name"]').value = 'Fahad Abdullah';
        document.querySelector('input[name="national_id"]').value = '1098765432';
        document.querySelector('input[name="phone"]').value = '0501234567';
        document.querySelector('input[name="email"]').value = 'fahad@example.com';
        document.querySelector('input[name="host_name"]').value = 'Mohamed Sabry';
        document.querySelector('input[name="purpose"]').value = 'Server Maintenance';
        document.querySelector('input[name="car_model"]').value = '2024 Changan';
        document.querySelector('input[name="plate_number"]').value = 'ABC 1234';
        document.querySelector('input[name="arrival"]').value = '08:00';
        document.querySelector('input[name="departure"]').value = '15:00';
    }
    function toggleDetails(btn) {
        const panel = btn.parentElement.querySelector('.details-panel');
        const arrow  = btn.querySelector('.arrow');
        const isOpen = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden', isOpen);
        arrow.style.transform = isOpen ? '' : 'rotate(180deg)';
        btn.querySelector('.btn-label').textContent = isOpen ? 'Show details' : 'Hide details';
    }
    </script>
</head>
<body class="bg-[#0B1120] flex min-h-screen text-slate-300 overflow-x-hidden">
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none -z-10"></div>
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-8 md:p-12 z-10 relative">
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tighter">Live Requests</h1>
                <p class="text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Pending approvals</p>
            </div>
            <?php if ($isAdmin): ?>
            <button onclick="document.getElementById('modal').classList.remove('hidden')" class="px-6 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Walk-in
            </button>
            <?php endif; ?>
        </header>

        <?php if ($walkinError): ?>
        <div class="mb-6 flex items-center gap-4 bg-red-500/10 border border-red-500/30 rounded-2xl px-6 py-4">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-black text-red-400"><?php echo htmlspecialchars($walkinError); ?></p>
        </div>
        <?php endif; ?>

        <?php if (!$isAdmin): ?>
        <div class="mb-6 flex items-center gap-4 bg-slate-800/50 border border-slate-700 rounded-2xl px-6 py-4">
            <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <p class="text-sm font-black text-slate-400">Supervisor view — actions are disabled.</p>
        </div>
        <?php endif; ?>

        <div class="bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-800/80 shadow-xl overflow-hidden">
            <div class="divide-y divide-slate-800/50">
                <?php if(count($active) > 0): foreach($active as $r): ?>
                <div class="p-6 hover:bg-slate-800/20 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=<?php echo urlencode($r['tracking_id']); ?>&color=0f172a"
                             class="flex-shrink-0 rounded-xl border border-slate-700 bg-white p-1.5" width="90" height="90" alt="QR">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-black text-white text-base"><?php echo htmlspecialchars($r['full_name']); ?></span>
                                <span class="text-[9px] font-black font-mono text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-lg border border-blue-500/20"><?php echo htmlspecialchars($r['tracking_id']); ?></span>
                                <?php if (!empty($r['visit_date'])): ?>
                                <span class="text-[9px] font-black text-slate-400 bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700"><?php echo date('M d, Y', strtotime($r['visit_date'])); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs font-black text-blue-400 mb-3">
                                <?php echo date('h:i A', strtotime($r['arrival_time'])); ?>
                                <span class="text-slate-400 mx-1">→</span>
                                <?php echo date('h:i A', strtotime($r['departure_time'])); ?>
                            </p>
                            <button onclick="toggleDetails(this)" class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 hover:text-blue-400 uppercase tracking-widest transition-colors">
                                <svg class="arrow w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                <span class="btn-label">Show details</span>
                            </button>
                            <div class="details-panel hidden mt-3 pt-3 border-t border-slate-800 grid grid-cols-2 gap-x-6 gap-y-3">
                                <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">National ID</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['national_id']); ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Phone</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['phone']); ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Host</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['host_name']); ?></p></div>
                                <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Purpose</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['purpose']); ?></p></div>
                                <?php if (!empty($r['vehicle_details'])): ?>
                                <div class="col-span-2"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Vehicle</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['vehicle_details']); ?></p></div>
                                <?php endif; ?>
                                <div class="col-span-2"><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Email</p><p class="text-xs font-bold text-slate-300"><?php echo htmlspecialchars($r['email']); ?></p></div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex flex-col gap-2">
                            <?php if ($isAdmin): ?>
                            <a href="?action=approve&id=<?php echo $r['id']; ?>" class="px-5 py-2.5 bg-green-500/10 text-green-400 hover:bg-green-600 hover:text-white border border-green-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-center">Approve</a>
                            <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-5 py-2.5 bg-red-500/10 text-red-400 hover:bg-red-600 hover:text-white border border-red-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-center">Reject</a>
                            <?php else: ?>
                            <div class="px-5 py-2.5 bg-slate-800 text-slate-600 border border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed text-center" title="Supervisor view only">Approve</div>
                            <div class="px-5 py-2.5 bg-slate-800 text-slate-600 border border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed text-center" title="Supervisor view only">Reject</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="p-12 text-center text-slate-500"><p class="text-sm font-bold uppercase tracking-widest">No pending requests</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php if ($isAdmin): ?>
    <div id="modal" class="fixed inset-0 bg-[#0B1120]/80 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-[2.5rem] w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="p-6 md:p-8 border-b border-slate-800 flex justify-between items-center bg-slate-800/20 shrink-0">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-white tracking-tight">Direct Registration</h2>
                    <button type="button" onclick="autoFillVisitor()" class="px-3 py-1.5 bg-blue-500/10 text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-500/20 transition-all border border-blue-500/20">Auto Fill</button>
                </div>
                <button onclick="document.getElementById('modal').classList.add('hidden')" class="text-slate-400 hover:text-white transition-colors bg-slate-800 p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-6 md:p-8">
                <form action="index.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2"><h3 class="text-xs font-black text-white uppercase tracking-widest border-b border-slate-700/50 pb-2">1. Personal Info</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Full Name</label><input type="text" name="name" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">ID Number</label><input type="text" name="national_id" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Phone Number</label><input type="tel" name="phone" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Email Address</label><input type="email" name="email" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>

                        <div class="md:col-span-2 mt-2"><h3 class="text-xs font-black text-white uppercase tracking-widest border-b border-slate-700/50 pb-2">2. Visit Details</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Host Name</label><input type="text" name="host_name" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Purpose of Visit</label><input type="text" name="purpose" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-blue-400 mb-2 ml-1">Visit Date</label><input type="date" name="visit_date" id="walkinDate" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-black text-sm"></div>
                        <div></div>
                        <div><label class="block text-[10px] font-black uppercase text-blue-400 mb-2 ml-1">Arrival Time</label><input type="time" name="arrival" min="07:00" max="15:30" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-black text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-blue-400 mb-2 ml-1">Departure Time</label><input type="time" name="departure" min="07:00" max="15:30" required class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-black text-sm"></div>

                        <div class="md:col-span-2 mt-2"><h3 class="text-xs font-black text-white uppercase tracking-widest border-b border-slate-700/50 pb-2">3. Vehicle (Optional)</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Car Model</label><input type="text" name="car_model" placeholder="e.g. Changan" class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Plate Number</label><input type="text" name="plate_number" placeholder="e.g. ABC 1234" class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm uppercase"></div>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-800">
                        <button type="submit" name="add_visitor" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl uppercase tracking-widest text-xs hover:bg-blue-700 active:scale-[0.98] transition-all">Register & Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    (function() {
        const dateInput = document.getElementById('walkinDate');
        if (!dateInput) return;
        const today = new Date().toISOString().split('T')[0];
        dateInput.min   = today;
        dateInput.value = today;
        dateInput.addEventListener('change', function() {
            const day = new Date(this.value + 'T00:00:00').getDay();
            if (day === 5 || day === 6) {
                alert('Friday and Saturday are weekends. Please select Sunday to Thursday.');
                this.value = today;
            }
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>
