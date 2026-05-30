<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: ../login.php"); 
    exit(); 
}
include '../includes/db.php';
include '../includes/mailer.php'; // استدعاء ملف الإيميل

// إضافة Walk-in وإرسال إيميل
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_visitor'])) {
    $tid = "ADM-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $vehicle_details = trim($_POST['car_model'] . ' ' . $_POST['plate_number']);
    
    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, purpose, vehicle_details, arrival_time, departure_time, tracking_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->execute([
        $_POST['name'], $_POST['phone'], $_POST['host_name'], $_POST['national_id'], $_POST['email'],
        $_POST['purpose'], $vehicle_details, $_POST['arrival'], $_POST['departure'], $tid
    ]);
    
    // محاولة إرسال الإيميل بصمت عشان ما يوقف الكود
    if(function_exists('sendVisitorEmail')) {
        @sendVisitorEmail($_POST['email'], $_POST['name'], $tid, 'approved');
    }
    header("Location: index.php?success=1"); 
    exit();
}

// قبول أو رفض الطلبات المعلقة وإرسال إيميل
if (isset($_GET['action']) && isset($_GET['id'])) {
    $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("SELECT email, full_name, tracking_id FROM visitors WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $v = $stmt->fetch();
    
    if ($v) {
        $conn->prepare("UPDATE visitors SET status = ? WHERE id = ?")->execute([$status, $_GET['id']]);
        if(function_exists('sendVisitorEmail')) {
            @sendVisitorEmail($v['email'], $v['full_name'], $v['tracking_id'], $status);
        }
    }
    header("Location: index.php"); 
    exit();
}

$active = $conn->query("SELECT * FROM visitors WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Requests - Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark') }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }
        
        function autoFillVisitor() {
            document.querySelector('input[name="name"]').value = 'Fahad Abdullah';
            document.querySelector('input[name="national_id"]').value = '1098765432';
            document.querySelector('input[name="phone"]').value = '0501234567';
            document.querySelector('input[name="email"]').value = 'fahad@example.com';
            document.querySelector('input[name="host_name"]').value = 'Mohamed Sabry';
            document.querySelector('input[name="purpose"]').value = 'Server Maintenance';
            document.querySelector('input[name="car_model"]').value = '2024 Changan Eado Plus';
            document.querySelector('input[name="plate_number"]').value = 'ABC 1234';
            document.querySelector('input[name="arrival"]').value = '08:00';
            document.querySelector('input[name="departure"]').value = '15:00';
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.3s; } .font-style-normal { font-style: normal !important; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] flex min-h-screen text-slate-800 dark:text-slate-300 transition-colors duration-300 selection:bg-blue-500 selection:text-white overflow-x-hidden">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none -z-10"></div>
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-8 md:p-12 transition-all z-10 relative">
        <header class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div><h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">Live Requests</h1><p class="text-slate-500 dark:text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Pending approvals</p></div>
            <button onclick="document.getElementById('modal').classList.remove('hidden')" class="px-6 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Add Walk-in
            </button>
        </header>

        <div class="bg-white dark:bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl dark:shadow-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700/50">
                        <tr class="uppercase text-[10px] font-black tracking-widest text-slate-500 dark:text-slate-400"><th class="p-6">Visitor Details</th><th class="p-6">Purpose</th><th class="p-6">Time</th><th class="p-6 text-center">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        <?php if(count($active) > 0): foreach($active as $r): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="p-6"><div class="font-black text-slate-800 dark:text-white text-lg mb-1"><?php echo htmlspecialchars($r['full_name']); ?></div><div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">ID: <?php echo htmlspecialchars($r['national_id']); ?></div></td>
                            <td class="p-6"><div class="text-sm font-bold text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($r['purpose']); ?></div><div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Host: <?php echo htmlspecialchars($r['host_name']); ?></div></td>
                            <td class="p-6 text-sm font-black text-blue-600 dark:text-blue-400"><?php echo date('h:i A', strtotime($r['arrival_time'])); ?></td>
                            <td class="p-6 flex justify-center gap-3">
                                <a href="?action=approve&id=<?php echo $r['id']; ?>" class="px-5 py-2.5 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-500 hover:bg-green-600 hover:text-white border border-green-200 dark:border-green-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Approve</a>
                                <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-5 py-2.5 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-500 hover:bg-red-600 hover:text-white border border-red-200 dark:border-red-500/20 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="p-12 text-center text-slate-500"><p class="text-sm font-bold uppercase tracking-widest">No pending requests</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Registration -->
    <div id="modal" class="fixed inset-0 bg-slate-900/60 dark:bg-[#0B1120]/80 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[2.5rem] w-full max-w-3xl shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 md:p-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/20 shrink-0">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Direct Registration</h2>
                    <button type="button" onclick="autoFillVisitor()" class="px-3 py-1.5 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-200 transition-all border border-blue-200 dark:border-blue-500/20">Auto Fill</button>
                </div>
                <button onclick="document.getElementById('modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors bg-slate-200 dark:bg-slate-800 p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-6 md:p-8">
                <form action="index.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2"><h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-b border-slate-200 dark:border-slate-700/50 pb-2">1. Personal Info</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Full Name</label><input type="text" name="name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">ID Number</label><input type="text" name="national_id" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Phone Number</label><input type="tel" name="phone" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Email Address</label><input type="email" name="email" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>

                        <div class="md:col-span-2 mt-2"><h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-b border-slate-200 dark:border-slate-700/50 pb-2">2. Visit Details</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Host Name</label><input type="text" name="host_name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Purpose of Visit</label><input type="text" name="purpose" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-blue-600 dark:text-blue-400 mb-2 ml-1">Arrival Time</label><input type="time" name="arrival" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-black text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-blue-600 dark:text-blue-400 mb-2 ml-1">Departure Time</label><input type="time" name="departure" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-black text-sm"></div>

                        <div class="md:col-span-2 mt-2"><h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-b border-slate-200 dark:border-slate-700/50 pb-2">3. Vehicle (Optional)</h3></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Car Model</label><input type="text" name="car_model" placeholder="e.g. Changan" class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm"></div>
                        <div><label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1">Plate Number</label><input type="text" name="plate_number" placeholder="e.g. ABC 1234" class="w-full p-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-slate-800 dark:text-white font-bold text-sm uppercase"></div>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-200 dark:border-slate-800">
                        <button type="submit" name="add_visitor" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl uppercase tracking-widest text-xs hover:bg-blue-700 active:scale-[0.98] transition-all">Register & Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>