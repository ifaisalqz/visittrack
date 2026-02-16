<?php
include 'includes/db.php';
$tid = $_GET['tid'] ?? '';
$stmt = $conn->prepare("SELECT * FROM visitors WHERE tracking_id = ?");
$stmt->execute([$tid]);
$v = $stmt->fetch();
if (!$v) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Status - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-lg bg-white rounded-[3rem] shadow-2xl p-12 text-center border relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-blue-600"></div>
        <h2 class="text-3xl font-extrabold text-slate-800 mb-2">Hello, <?php echo htmlspecialchars($v['full_name']); ?></h2>
        <p class="text-blue-600 font-mono font-bold text-lg mb-10 tracking-widest uppercase">ID: <?php echo $tid; ?></p>
        
        <div class="p-8 rounded-[2rem] mb-8 <?php 
            echo ($v['status'] == 'approved') ? 'bg-green-50 border-2 border-green-100 text-green-700' : 
                (($v['status'] == 'expired') ? 'bg-slate-50 border-2 border-slate-100 text-slate-400' : 'bg-blue-50 border-2 border-blue-200 text-blue-700'); 
        ?> transition-all duration-500">
            <span class="text-[10px] uppercase font-black tracking-[0.2em] opacity-50 block mb-2">Ticket Status</span>
            <p class="text-4xl font-black uppercase italic">
                <?php echo ($v['status'] == 'approved') ? 'Checked-In' : $v['status']; ?>
            </p>
            <?php if($v['actual_checkin'] && $v['status'] == 'approved'): ?>
                <p class="text-[10px] font-bold mt-2 uppercase tracking-widest italic">Entered: <?php echo date('h:i A', strtotime($v['actual_checkin'])); ?></p>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-10 text-sm font-bold text-slate-700">
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block text-[10px] uppercase mb-2 italic">Planned Entry</span>
                <?php echo date('h:i A', strtotime($v['arrival_time'])); ?>
            </div>
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                <span class="text-slate-400 block text-[10px] uppercase mb-2 italic">Planned Exit</span>
                <?php echo date('h:i A', strtotime($v['departure_time'])); ?>
            </div>
        </div>

        <button onclick="location.reload()" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-black transition active:scale-95 shadow-xl">Refresh Status</button>
        <p class="mt-6 text-[10px] text-slate-300 font-bold uppercase tracking-widest">Auto-updating every 10 seconds</p>
    </div>
    <script>setTimeout(() => { location.reload(); }, 10000);</script>
</body>
</html>