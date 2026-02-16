<?php
session_start();

// التحقق من الأمان
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

// جلب الإحصائيات السريعة للأعلى
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM visitors WHERE status IN ('expired', 'rejected')")->fetch();

// جلب السجلات مع حساب فرق الوقت الفعلي
$query = "SELECT *, 
          TIMESTAMPDIFF(MINUTE, actual_checkin, actual_checkout) as total_minutes 
          FROM visitors 
          WHERE status IN ('expired', 'rejected') 
          ORDER BY actual_checkout DESC, created_at DESC";

$logs = $conn->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs History - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-72 bg-slate-900 text-white p-8 sticky top-0 h-screen flex flex-col shadow-2xl">
        <div class="mb-12">
            <h2 class="text-2xl font-black text-blue-400 tracking-tighter italic">VISIT TRACK</h2>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em] mt-2 text-center">Admin Panel</p>
        </div>
        
        <nav class="space-y-3 flex-1 text-sm">
            <a href="index.php" class="flex items-center gap-4 p-4 text-slate-400 hover:bg-slate-800 hover:text-white rounded-2xl transition font-bold">
                Live Requests
            </a>
            <a href="logs.php" class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg transition font-bold italic">
                History Logs
            </a>
        </nav>

        <a href="logout.php" class="p-4 text-red-400 hover:bg-red-950/30 rounded-2xl transition font-black uppercase text-[10px] text-center border border-red-900/30 tracking-widest">
            Logout
        </a>
    </aside>

    <main class="flex-1 p-12">
        <header class="mb-12 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">System Logs</h1>
                <p class="text-slate-400 mt-2 font-medium italic uppercase text-[10px] tracking-widest">Archive of all completed and rejected visits</p>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Archive</p>
                <h3 class="text-3xl font-black text-slate-800"><?php echo $stats['total']; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 border-l-4 border-l-green-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Completed Visits</p>
                <h3 class="text-3xl font-black text-green-600"><?php echo (int)$stats['completed']; ?></h3>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 border-l-4 border-l-red-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rejected Requests</p>
                <h3 class="text-3xl font-black text-red-600"><?php echo (int)$stats['rejected']; ?></h3>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                        <th class="p-6">Visitor & Ticket</th>
                        <th class="p-6">Status</th>
                        <th class="p-6">Actual Check-In/Out</th>
                        <th class="p-6 text-center">Total Time</th>
                        <th class="p-6 text-right">Logging Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (count($logs) > 0): ?>
                        <?php foreach($logs as $r): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-6">
                                <div class="font-black text-slate-800 uppercase italic tracking-tighter text-lg"><?php echo htmlspecialchars($r['full_name']); ?></div>
                                <div class="text-[10px] font-bold text-blue-500 mt-1 uppercase">ID: <?php echo $r['tracking_id']; ?></div>
                            </td>
                            <td class="p-6">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest <?php 
                                    echo $r['status']=='expired' ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100'; 
                                ?>">
                                    <?php echo $r['status']; ?>
                                </span>
                            </td>
                            <td class="p-6">
                                <?php if($r['actual_checkin']): ?>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-[11px] font-bold text-slate-600">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            IN: <?php echo date('h:i A', strtotime($r['actual_checkin'])); ?>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            OUT: <?php echo $r['actual_checkout'] ? date('h:i A', strtotime($r['actual_checkout'])) : 'N/A'; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 italic text-[11px] uppercase font-bold tracking-widest">Never Entered</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 text-center">
                                <?php if($r['actual_checkout']): ?>
                                    <div class="inline-block px-4 py-2 bg-blue-50 rounded-2xl border border-blue-100">
                                        <div class="font-black text-blue-600 text-base uppercase italic">
                                            <?php 
                                                $h = floor($r['total_minutes'] / 60);
                                                $m = $r['total_minutes'] % 60;
                                                echo ($h > 0 ? "{$h}h " : "") . "{$m}m"; 
                                            ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-200">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-6 text-right">
                                <div class="text-[11px] font-black text-slate-400 uppercase italic">
                                    <?php echo date('d M, Y', strtotime($r['created_at'])); ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-32 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-2xl font-bold italic uppercase tracking-widest">No history logs yet</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>