<?php
include 'includes/db.php';

$visitor = null;
$error = false;

if (isset($_GET['tid'])) {
    $tid = htmlspecialchars(trim($_GET['tid']));
    $stmt = $conn->prepare("SELECT * FROM visitors WHERE tracking_id = ?");
    $stmt->execute([$tid]);
    $visitor = $stmt->fetch();
    if (!$visitor) { $error = true; }
} else {
    header("Location: track.php"); exit();
}

// إعداد ألوان ونصوص الحالة
$statusColor = 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border-yellow-200 dark:border-yellow-500/20';
$statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
$statusText  = 'Pending';

if ($visitor) {
    $hasCheckin  = !empty($visitor['actual_checkin']);
    $hasCheckout = !empty($visitor['actual_checkout']);

    if ($visitor['status'] === 'rejected') {
        $statusColor = 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/20';
        $statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        $statusText  = 'Rejected';

    } elseif ($visitor['status'] === 'expired') {
        $statusColor = 'bg-orange-50 dark:bg-orange-500/10 text-orange-500 dark:text-orange-400 border-orange-200 dark:border-orange-500/20';
        $statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        $statusText  = 'Expired';

    } elseif ($visitor['status'] === 'completed' || $hasCheckout) {
        $statusColor = 'bg-slate-100 dark:bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-500/20';
        $statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>';
        $statusText  = 'Checked Out';

    } elseif ($hasCheckin) {
        $statusColor = 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20';
        $statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>';
        $statusText  = 'Checked In';

    } elseif ($visitor['status'] === 'approved') {
        $statusColor = 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 border-green-200 dark:border-green-500/20';
        $statusIcon  = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
        $statusText  = 'Approved';

    } else {
        // pending (default)
        $statusText = 'Pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Pass - <?php echo htmlspecialchars($tid); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; } .font-style-normal { font-style: normal !important; }</style>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] min-h-screen flex flex-col transition-colors duration-300 selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/20 dark:bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <nav class="w-full max-w-7xl mx-auto p-6 md:px-12 flex justify-between items-center relative z-10">
        <a href="index.php" class="text-2xl font-black text-slate-800 dark:text-white tracking-tighter font-style-normal flex items-center gap-2 hover:opacity-80 transition-opacity">
            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20"><svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
            VISIT TRACK
        </a>
        <div class="flex items-center gap-4">
            <button onclick="toggleDarkMode()" class="p-3 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-xl transition-all">
                <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707 .707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
            </button>
            <a href="index.php" class="px-4 py-2.5 text-xs font-black uppercase tracking-widest text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors font-style-normal">Home</a>
        </div>
    </nav>

    <main class="flex-1 flex flex-col items-center justify-center p-6 relative z-10">
        <?php if ($error): ?>
            <div class="max-w-md w-full bg-white dark:bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl p-10 text-center border border-slate-100 dark:border-slate-800">
                <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-white mb-2">Not Found</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-8">No request found for: <strong><?php echo htmlspecialchars($tid); ?></strong></p>
                <a href="track.php" class="w-full block py-4 bg-slate-800 dark:bg-blue-600 text-white font-black rounded-2xl uppercase tracking-widest text-xs hover:opacity-90 transition-opacity">Try Again</a>
            </div>
        <?php else: ?>
            <div class="max-w-sm w-full bg-white dark:bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-slate-100 dark:border-slate-800/80 overflow-hidden relative">
                <div class="p-8 pb-6 text-center border-b-2 border-dashed border-slate-200 dark:border-slate-700/50 relative">
                    <div class="absolute -bottom-4 -left-4 w-8 h-8 bg-slate-50 dark:bg-[#0B1120] rounded-full border-r border-slate-200 dark:border-slate-800/80"></div>
                    <div class="absolute -bottom-4 -right-4 w-8 h-8 bg-slate-50 dark:bg-[#0B1120] rounded-full border-l border-slate-200 dark:border-slate-800/80"></div>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest mb-6 border <?php echo $statusColor; ?> font-style-normal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $statusIcon; ?></svg>
                        <?php echo $statusText; ?>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1 font-style-normal">Visitor Name</p>
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight leading-tight font-style-normal"><?php echo htmlspecialchars($visitor['full_name']); ?></h2>
                </div>

                <div class="p-8 text-center bg-slate-50/50 dark:bg-[#0B1120]/30">
                    <?php if ($visitor['status'] == 'approved' || $visitor['status'] == 'active' || $visitor['status'] == 'pending'): ?>
                        <div class="bg-white p-4 rounded-3xl shadow-sm inline-block border border-slate-100 dark:border-slate-700">
                            <div id="qr-active" class="rounded-xl w-40 h-40 flex items-center justify-center"></div>
                        </div>
                        <p class="mt-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tracking ID</p>
                        <p class="text-xl font-black text-slate-800 dark:text-white tracking-[0.2em] mt-1 uppercase font-mono"><?php echo htmlspecialchars($visitor['tracking_id']); ?></p>
                    <?php elseif ($visitor['status'] == 'expired' || $visitor['status'] == 'completed'): ?>
                        <div class="bg-white p-4 rounded-3xl shadow-sm inline-block border border-slate-100 dark:border-slate-700 opacity-40 grayscale">
                            <div id="qr-archived" class="rounded-xl w-40 h-40 flex items-center justify-center"></div>
                        </div>
                        <p class="mt-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Archived Pass</p>
                    <?php else: ?>
                        <div class="w-40 h-40 bg-slate-100 dark:bg-slate-800/50 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-700/50">
                            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tracking ID</p>
                        <p class="text-lg font-black text-slate-600 dark:text-slate-400 tracking-[0.2em] mt-1 uppercase font-mono"><?php echo htmlspecialchars($visitor['tracking_id']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="p-8 bg-transparent border-t border-slate-100 dark:border-slate-800/50">
                    <div class="grid grid-cols-2 gap-y-6 gap-x-4 text-left">
                        <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 font-style-normal">Host</p><p class="text-sm font-black text-slate-800 dark:text-white"><?php echo htmlspecialchars($visitor['host_name']); ?></p></div>
                        <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 font-style-normal">Purpose</p><p class="text-sm font-bold text-slate-700 dark:text-slate-300 truncate"><?php echo htmlspecialchars($visitor['purpose']); ?></p></div>
                        <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 font-style-normal">Time</p><p class="text-xs font-black text-blue-600 dark:text-blue-400"><?php echo date('h:i A', strtotime($visitor['arrival_time'])); ?></p></div>
                        <div><p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 font-style-normal">Vehicle</p><p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate font-mono"><?php echo !empty($visitor['vehicle_details']) ? htmlspecialchars($visitor['vehicle_details']) : 'N/A'; ?></p></div>
                    </div>
                </div>
            </div>
            
<div class="mt-8">
    <a href="track.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-black rounded-2xl shadow-sm hover:border-blue-500 dark:hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 hover:scale-[1.02] transition-all text-xs uppercase tracking-widest font-style-normal">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        Search Another Request
    </a>
</div>

            <button onclick="window.print()" class="fixed bottom-6 right-6 w-12 h-12 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
        <?php endif; ?>
    </main>

    <style>
        @media print {
            nav, button, .fixed { display: none !important; }
            body { background: white !important; }
            .shadow-2xl { box-shadow: none !important; border: 2px solid #e2e8f0; }
        }
    </style>
    <script>
        const tid = <?php echo json_encode($visitor['tracking_id'] ?? ''); ?>;
        const qrOptions = { width: 160, height: 160, colorDark: '#0f172a', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H };

        const elActive   = document.getElementById('qr-active');
        const elArchived = document.getElementById('qr-archived');

        if (elActive   && tid) new QRCode(elActive,   Object.assign({ text: tid }, qrOptions));
        if (elArchived && tid) new QRCode(elArchived, Object.assign({ text: tid }, qrOptions));
    </script>
</body>
</html>