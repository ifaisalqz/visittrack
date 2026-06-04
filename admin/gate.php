<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

$expected = $conn->query("SELECT * FROM visitors WHERE visit_date = CURDATE() AND actual_checkin IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY arrival_time ASC")->fetchAll();
$inside   = $conn->query("SELECT * FROM visitors WHERE actual_checkin IS NOT NULL AND actual_checkout IS NULL AND status NOT IN ('rejected','expired','completed') ORDER BY actual_checkin DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Gate Display — VisitTrack</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script>tailwind.config = { darkMode: 'class' }</script>
<style>body { font-family:'Plus Jakarta Sans',sans-serif; background:#0B1120; color:white; overflow:hidden; } .scrollable { overflow-y:auto; max-height:calc(100vh - 180px); scrollbar-width:none; } .scrollable::-webkit-scrollbar { display:none; }</style>
</head>
<body class="min-h-screen flex flex-col">
    <div class="flex items-center justify-between px-12 py-6 border-b border-slate-800">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-2xl font-black tracking-tighter text-white">VISIT TRACK</span>
        </div>
        <div class="text-right">
            <div id="clock" class="text-3xl font-black text-white font-mono tracking-widest"></div>
            <div id="date-display" class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1"></div>
        </div>
    </div>

    <div class="flex-1 grid grid-cols-2 gap-0 overflow-hidden">
        <div class="border-r border-slate-800 flex flex-col">
            <div class="px-10 py-5 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Expected Today</h2>
                <span class="px-3 py-1 bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 rounded-full text-xs font-black"><?php echo count($expected); ?> Waiting</span>
            </div>
            <div class="scrollable px-6 py-4 space-y-3">
                <?php if (empty($expected)): ?><div class="flex items-center justify-center h-32 text-slate-600 text-sm font-bold uppercase tracking-widest">No visitors waiting</div><?php endif; ?>
                <?php foreach($expected as $v): ?>
                <div class="bg-slate-900/50 border border-slate-800 rounded-2xl px-6 py-4 flex items-center gap-5">
                    <div class="w-12 h-12 bg-blue-600/10 border border-blue-600/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-lg font-black text-blue-400"><?php echo strtoupper(mb_substr($v['full_name'],0,1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-black text-white text-base truncate"><?php echo htmlspecialchars($v['full_name']); ?></div>
                        <div class="text-xs text-slate-500 font-bold mt-0.5 truncate"><?php echo htmlspecialchars($v['purpose']); ?> · <?php echo htmlspecialchars($v['host_name']); ?></div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-black text-blue-400"><?php echo date('h:i A', strtotime($v['arrival_time'])); ?></div>
                        <div class="text-[10px] text-slate-600 font-bold uppercase tracking-widest"><?php echo htmlspecialchars($v['tracking_id']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex flex-col">
            <div class="px-10 py-5 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Currently Inside</h2>
                <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-xs font-black animate-pulse"><?php echo count($inside); ?> Active</span>
            </div>
            <div class="scrollable px-6 py-4 space-y-3">
                <?php if (empty($inside)): ?><div class="flex items-center justify-center h-32 text-slate-600 text-sm font-bold uppercase tracking-widest">Facility is empty</div><?php endif; ?>
                <?php foreach($inside as $v):
                    $elapsed = time() - strtotime($v['actual_checkin']);
                    $em = floor($elapsed/60); $eh = floor($em/60); $em = $em%60;
                    $elapsedStr = ($eh > 0 ? $eh.'h ' : '') . $em . 'm';
                ?>
                <div class="bg-green-500/5 border border-green-500/15 rounded-2xl px-6 py-4 flex items-center gap-5">
                    <div class="w-12 h-12 bg-green-500/10 border border-green-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-lg font-black text-green-400"><?php echo strtoupper(mb_substr($v['full_name'],0,1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-black text-white text-base truncate"><?php echo htmlspecialchars($v['full_name']); ?></div>
                        <div class="text-xs text-slate-500 font-bold mt-0.5 truncate"><?php echo htmlspecialchars($v['purpose']); ?> · <?php echo htmlspecialchars($v['host_name']); ?></div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-black text-green-400">In at <?php echo date('h:i A', strtotime($v['actual_checkin'])); ?></div>
                        <div class="text-[10px] text-slate-600 font-bold uppercase tracking-widest"><?php echo $elapsedStr; ?> elapsed</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="px-12 py-3 border-t border-slate-800 flex items-center justify-between">
        <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">Auto-refresh every 30 seconds</span>
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-[10px] font-bold text-slate-600">Live</span>
        </div>
        <a href="waiting_list.php" class="text-[10px] font-black uppercase tracking-widest text-slate-700 hover:text-white transition-colors">Back to Admin</a>
    </div>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });
    document.getElementById('date-display').textContent = now.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
updateClock();
setInterval(updateClock, 1000);
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
