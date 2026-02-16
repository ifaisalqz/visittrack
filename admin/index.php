<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

// 1. معالجة إضافة زائر جديد من قبل الأدمن
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_visitor'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $purpose = htmlspecialchars($_POST['purpose']);
    $arrival = $_POST['arrival'];
    $departure = $_POST['departure'];
    
    $time1 = strtotime($arrival);
    $time2 = strtotime($departure);
    $duration_hours = round(($time2 - $time1) / 3600, 1);
    $tid = "ADM-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $purpose, $duration_hours, $arrival, $departure, $tid]);
    header("Location: index.php"); exit();
}

// 2. معالجة عمليات الدخول والخروج والرفض
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $now = date('Y-m-d H:i:s');
    if ($_GET['action'] == 'checkin') {
        $stmt = $conn->prepare("UPDATE visitors SET status = 'approved', actual_checkin = ? WHERE id = ?");
        $stmt->execute([$now, $id]);
    } elseif ($_GET['action'] == 'checkout') {
        $stmt = $conn->prepare("UPDATE visitors SET status = 'expired', actual_checkout = ? WHERE id = ?");
        $stmt->execute([$now, $id]);
    } elseif ($_GET['action'] == 'rejected') {
        $conn->prepare("UPDATE visitors SET status = 'rejected' WHERE id = ?")->execute([$id]);
    }
    header("Location: index.php"); exit();
}

$active = $conn->query("SELECT * FROM visitors WHERE status IN ('pending', 'approved') ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Management - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-y: hidden; }
    </style>
</head>
<body class="bg-slate-50 flex min-h-screen">
    
    <aside class="w-72 bg-slate-900 text-white p-8 sticky top-0 flex flex-col shadow-2xl">
        <div class="mb-12">
            <h2 class="text-2xl font-black text-blue-400 tracking-tighter">VISIT TRACK</h2>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em] mt-2">Admin Dashboard</p>
        </div>
        <nav class="space-y-3 flex-1 text-sm">
            <a href="index.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg transition font-bold ">Live Requests</a>
            <a href="logs.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 hover:text-white rounded-2xl transition font-bold">History Logs</a>
        </nav>
        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest">Logout</a>
    </aside>

    <main class="flex-1 p-12">
        <header class="mb-12 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Active Management</h1>
                <p class="text-slate-400 mt-2 font-medium  uppercase text-[10px] tracking-[0.2em]">Real-time visitor monitoring</p>
            </div>
            <button onclick="toggleModal()" class="px-6 py-4 bg-slate-800 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-blue-600 transition-all flex items-center gap-2">
                <span class="text-lg">+</span> Add New Visitor
            </button>
        </header>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6">Visitor</th>
                        <th class="p-6">Planned Time</th>
                        <th class="p-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach($active as $r): ?>
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 text-lg  uppercase tracking-tighter"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest "><?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-black text-blue-600  uppercase"><?php echo date('h:i A', strtotime($r['arrival_time'])); ?> - <?php echo date('h:i A', strtotime($r['departure_time'])); ?></div>
                            <div class="text-[9px] font-bold text-slate-300 mt-1 uppercase">Actual Status: <?php echo $r['status']; ?></div>
                        </td>
                        <td class="p-6 flex justify-center gap-3">
                            <?php if($r['status'] == 'pending'): ?>
                                <a href="?action=checkin&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-200">Confirm Entry</a>
                                <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-white border border-slate-200 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest">Reject</a>
                            <?php elseif($r['status'] == 'approved'): ?>
                                <a href="?action=checkout&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-orange-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-orange-200">Confirm Departure</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50">
        <div class="modal-overlay absolute w-full h-full bg-slate-900 opacity-50"></div>
        <div class="modal-container bg-white w-full max-w-lg mx-auto rounded-[2.5rem] shadow-2xl z-50 overflow-y-auto border p-10">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black  uppercase tracking-tighter">Add Walk-in Visitor</h3>
                <button onclick="toggleModal()" class="text-slate-400 hover:text-red-500 text-2xl font-bold">&times;</button>
            </div>
            
            <form action="index.php" method="POST" class="space-y-6">
                <input type="hidden" name="add_visitor" value="1">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Full Name</label>
                    <input type="text" name="name" required class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 ring-blue-500">
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Phone Number</label>
                    <input type="tel" name="phone" required class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">In</label>
                        <input type="time" name="arrival" required class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Out</label>
                        <input type="time" name="departure" required class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 ring-blue-500">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest ml-1">Purpose</label>
                    <textarea name="purpose" required rows="2" class="w-full p-4 bg-slate-50 border-none rounded-2xl outline-none focus:ring-2 ring-blue-500"></textarea>
                </div>
                <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-200 uppercase tracking-widest text-xs">Save Visitor</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('modal');
            const body = document.querySelector('body');
            modal.classList.toggle('opacity-0');
            modal.classList.toggle('pointer-events-none');
            body.classList.toggle('modal-active');
        }
    </script>
</body>
</html>