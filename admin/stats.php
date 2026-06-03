<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }
include '../includes/db.php';

// إحصائيات الزيارات آخر ٧ أيام
$daily = $conn->query("
    SELECT DATE(created_at) as day, COUNT(*) as total
    FROM visitors
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll();

// أوقات الذروة
$hours = $conn->query("
    SELECT HOUR(actual_checkin) as hr, COUNT(*) as total
    FROM visitors
    WHERE actual_checkin IS NOT NULL
    GROUP BY HOUR(actual_checkin)
    ORDER BY hr ASC
")->fetchAll();

// توزيع الحالات
$statuses = $conn->query("
    SELECT status, COUNT(*) as total FROM visitors GROUP BY status
")->fetchAll();

// أرقام سريعة
$todayTotal   = $conn->query("SELECT COUNT(*) FROM visitors WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$todayInside  = $conn->query("SELECT COUNT(*) FROM visitors WHERE actual_checkin IS NOT NULL AND actual_checkout IS NULL")->fetchColumn();
$monthTotal   = $conn->query("SELECT COUNT(*) FROM visitors WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
$avgDuration  = $conn->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, actual_checkin, actual_checkout)) FROM visitors WHERE actual_checkin IS NOT NULL AND actual_checkout IS NOT NULL")->fetchColumn();
$avgDuration  = $avgDuration ? round($avgDuration) : 0;

// تجهيز البيانات لـ Charts
$dailyLabels = []; $dailyData = [];
// نضيف كل الأيام السبعة (حتى اللي ما فيها زيارات)
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $dailyLabels[] = date('M d', strtotime($day));
    $found = 0;
    foreach ($daily as $d) { if ($d['day'] === $day) { $found = $d['total']; break; } }
    $dailyData[] = $found;
}

$hourLabels = []; $hourData = [];
for ($h = 0; $h < 24; $h++) {
    $hourLabels[] = date('h A', mktime($h, 0, 0));
    $found = 0;
    foreach ($hours as $r) { if ((int)$r['hr'] === $h) { $found = $r['total']; break; } }
    $hourData[] = $found;
}

$statusLabels = []; $statusData = []; $statusColors = [];
$colorMap = ['pending'=>'#f59e0b','approved'=>'#10b981','completed'=>'#2563eb','rejected'=>'#ef4444','expired'=>'#94a3b8','active'=>'#8b5cf6'];
foreach ($statuses as $s) {
    $statusLabels[] = ucfirst($s['status']);
    $statusData[]   = $s['total'];
    $statusColors[] = $colorMap[$s['status']] ?? '#64748b';
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Statistics - Admin Portal</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<script>
    tailwind.config = { darkMode:'class' }
    if (localStorage.theme==='dark'||(!('theme' in localStorage)&&window.matchMedia('(prefers-color-scheme:dark)').matches)){document.documentElement.classList.add('dark')}
    function toggleDarkMode(){document.documentElement.classList.toggle('dark');localStorage.theme=document.documentElement.classList.contains('dark')?'dark':'light';}
</script>
<style>body{font-family:'Plus Jakarta Sans',sans-serif;transition:background-color .3s;}</style>
</head>
<body class="bg-slate-50 dark:bg-[#0B1120] flex min-h-screen text-slate-800 dark:text-slate-300 transition-colors duration-300 overflow-x-hidden">
<div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/10 blur-[150px] rounded-full pointer-events-none -z-10"></div>
<?php include '../includes/admin_nav.php'; ?>

<main class="flex-1 p-8 md:p-12 z-10 relative">
    <header class="mb-12">
        <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tighter">Statistics</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Visitor analytics & insights</p>
    </header>

    <!-- Quick numbers -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
        <div class="bg-white dark:bg-slate-900/60 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800/80 shadow-xl">
            <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-2">اليوم</p>
            <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $todayTotal; ?></div>
            <p class="text-[9px] text-slate-400 mt-1">زيارة مسجلة</p>
        </div>
        <div class="bg-white dark:bg-slate-900/60 p-6 rounded-[2rem] border border-green-200 dark:border-green-500/20 shadow-xl">
            <p class="text-[9px] font-black uppercase tracking-widest text-green-600 mb-2">داخل الآن</p>
            <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $todayInside; ?></div>
            <p class="text-[9px] text-slate-400 mt-1">زائر حالياً</p>
        </div>
        <div class="bg-white dark:bg-slate-900/60 p-6 rounded-[2rem] border border-blue-200 dark:border-blue-500/20 shadow-xl">
            <p class="text-[9px] font-black uppercase tracking-widest text-blue-600 mb-2">هذا الشهر</p>
            <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $monthTotal; ?></div>
            <p class="text-[9px] text-slate-400 mt-1">زيارة إجمالية</p>
        </div>
        <div class="bg-white dark:bg-slate-900/60 p-6 rounded-[2rem] border border-purple-200 dark:border-purple-500/20 shadow-xl">
            <p class="text-[9px] font-black uppercase tracking-widest text-purple-600 mb-2">متوسط المدة</p>
            <div class="text-4xl font-black text-slate-800 dark:text-white"><?php echo $avgDuration; ?></div>
            <p class="text-[9px] text-slate-400 mt-1">دقيقة لكل زيارة</p>
        </div>
    </div>

    <!-- Charts row 1 -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <!-- Daily visits - takes 2 cols -->
        <div class="xl:col-span-2 bg-white dark:bg-slate-900/60 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6">الزيارات - آخر ٧ أيام</h3>
            <canvas id="dailyChart" height="100"></canvas>
        </div>
        <!-- Status donut -->
        <div class="bg-white dark:bg-slate-900/60 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl flex flex-col">
            <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6">توزيع الحالات</h3>
            <div class="flex-1 flex items-center justify-center">
                <canvas id="statusChart" style="max-height:220px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Peak hours -->
    <div class="bg-white dark:bg-slate-900/60 p-8 rounded-[2.5rem] border border-slate-200 dark:border-slate-800/80 shadow-xl">
        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-6">أوقات الذروة</h3>
        <canvas id="hoursChart" height="80"></canvas>
    </div>
</main>

<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor   = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const tickColor   = isDark ? '#64748b' : '#94a3b8';
const labelColor  = isDark ? '#e2e8f0' : '#1e293b';

Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";

// Daily
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dailyLabels); ?>,
        datasets: [{
            label: 'Visits',
            data: <?php echo json_encode($dailyData); ?>,
            backgroundColor: 'rgba(37,99,235,0.8)',
            borderRadius: 10,
            borderSkipped: false,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: tickColor } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 }, beginAtZero: true }
        }
    }
});

// Status donut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($statusLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($statusData); ?>,
            backgroundColor: <?php echo json_encode($statusColors); ?>,
            borderWidth: 0,
            hoverOffset: 8,
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { color: tickColor, padding: 16, font: { weight: '700', size: 11 } } }
        }
    }
});

// Hours
new Chart(document.getElementById('hoursChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($hourLabels); ?>,
        datasets: [{
            label: 'Check-ins',
            data: <?php echo json_encode($hourData); ?>,
            backgroundColor: 'rgba(16,185,129,0.75)',
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } },
            y: { grid: { color: gridColor }, ticks: { color: tickColor, stepSize: 1 }, beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
