<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; 
include '../includes/db.php';

// معالجة Walk-in
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_visitor'])) {
    $name = htmlspecialchars($_POST['name']); $phone = htmlspecialchars($_POST['phone']);
    $purpose = htmlspecialchars($_POST['purpose']); $arrival = $_POST['arrival']; $departure = $_POST['departure'];
    $host_name = htmlspecialchars($_POST['host_name']); $national_id = htmlspecialchars($_POST['national_id']);
    $email = htmlspecialchars($_POST['email']); $car_model = trim($_POST['car_model']); $plate_number = trim($_POST['plate_number']);
    $vehicle_details = (!empty($car_model) || !empty($plate_number)) ? htmlspecialchars($car_model . " | " . strtoupper($plate_number)) : "";
    $duration_hours = round((strtotime($departure) - strtotime($arrival)) / 3600, 1);
    $tid = "ADM-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, vehicle_details, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $phone, $host_name, $national_id, $email, $vehicle_details, $purpose, $duration_hours, $arrival, $departure, $tid])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP(); $mail->Host = 'smtp.office365.com'; $mail->SMTPAuth = true;
            $mail->Username = 'noreply@faisal.biz'; $mail->Password = 'bhwvxcvzsqmvnsgj';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
            $mail->setFrom('noreply@faisal.biz', 'Visit Track System'); $mail->addAddress($email, $name); 
            $mail->isHTML(true); $mail->Subject = 'Walk-in Visitor Pass - Visit Track';
            $mail->Body = '<div style="font-family: Arial; text-align: center; padding: 20px;"><h2>Welcome!</h2><p>Your tracking ID is: <b>'.$tid.'</b></p></div>';
            $mail->send();
        } catch (Exception $e) {}
        header("Location: index.php"); exit();
    }
}

// معالجة القبول والرفض
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'approve') {
        $v_stmt = $conn->prepare("SELECT full_name, email, tracking_id FROM visitors WHERE id = ?");
        $v_stmt->execute([$id]); $visitor = $v_stmt->fetch();
        if ($visitor) {
            $conn->prepare("UPDATE visitors SET status = 'approved' WHERE id = ?")->execute([$id]);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP(); $mail->Host = 'smtp.office365.com'; $mail->SMTPAuth = true;
                $mail->Username = 'noreply@faisal.biz'; $mail->Password = 'bhwvxcvzsqmvnsgj'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; $mail->Port = 587;
                $mail->setFrom('noreply@faisal.biz', 'Visit Track System'); $mail->addAddress($visitor['email'], $visitor['full_name']); 
                $mail->isHTML(true); $mail->Subject = 'Visit Request Approved';
                $mail->Body = '<div style="font-family: Arial; text-align: center; padding: 20px;"><h2>Approved!</h2><p>Your ID: <b>'.$visitor['tracking_id'].'</b></p></div>';
                $mail->send();
            } catch (Exception $e) {}
        }
        header("Location: waiting_list.php"); exit();
    } elseif ($_GET['action'] == 'rejected') {
        $conn->prepare("UPDATE visitors SET status = 'rejected' WHERE id = ?")->execute([$id]);
        header("Location: index.php"); exit();
    }
}
$active = $conn->query("SELECT * FROM visitors WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Requests - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) { document.documentElement.classList.add('dark'); }
        function toggleDarkMode() { document.documentElement.classList.toggle('dark'); localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light'; }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen transition-colors duration-300">
    
    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-12 overflow-x-hidden">
        <header class="mb-10 flex justify-between items-end text-slate-800 dark:text-white">
            <div>
                <h1 class="text-3xl font-black tracking-tight">Live Requests</h1>
                <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em]">Pending visitor approvals</p>
            </div>
            <button onclick="toggleModal()" class="px-6 py-3.5 bg-slate-800 dark:bg-blue-600 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-lg hover:bg-slate-700 dark:hover:bg-blue-500 transition-all duration-200">+ Add Walk-in</button>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Details</th>
                        <th class="p-6">Destination & Purpose</th>
                        <th class="p-6">Vehicle Access</th>
                        <th class="p-6">Planned Time</th>
                        <th class="p-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    <?php foreach($active as $r): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-200">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-base tracking-tight"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">ID: <?php echo htmlspecialchars($r['national_id']); ?> | Mob: <?php echo htmlspecialchars($r['phone']); ?></div>
                            <div class="text-[10px] text-slate-400 font-medium lowercase mt-0.5"><?php echo htmlspecialchars($r['email']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Host: <span class="text-blue-600 dark:text-blue-400 font-black"><?php echo htmlspecialchars($r['host_name']); ?></span></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-1">Purpose: <?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6">
                            <?php if(!empty($r['vehicle_details'])): ?>
                                <span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-[10px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 block text-center uppercase tracking-wide font-mono"><?php echo htmlspecialchars($r['vehicle_details']); ?></span>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-600 text-[11px] font-medium">No Vehicle</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6">
                            <div class="text-xs font-black text-blue-600 dark:text-blue-400 tracking-wide"><?php echo date('h:i A', strtotime($r['arrival_time'])); ?> - <?php echo date('h:i A', strtotime($r['departure_time'])); ?></div>
                        </td>
                        <td class="p-6 flex justify-center gap-2 items-center min-h-[90px]">
                            <a href="?action=approve&id=<?php echo $r['id']; ?>" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-[10px] font-black uppercase shadow-md hover:bg-blue-700 hover:shadow-lg transition-all">Approve</a>
                            <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-red-500 rounded-lg text-[10px] font-black uppercase hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">Reject</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($active)) echo "<tr><td colspan='5' class='p-10 text-center text-slate-400 font-medium'>No pending requests found</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal" class="modal opacity-0 pointer-events-none fixed inset-0 flex items-center justify-center z-[100] transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal()"></div>
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-xl mx-auto rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 p-8 max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-6 tracking-tight">Add Walk-in Visitor</h3>
            <form action="index.php" method="POST" class="space-y-4">
                <input type="hidden" name="add_visitor" value="1">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="t_name" name="name" placeholder="Full Name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                    <input type="text" id="t_nid" name="national_id" placeholder="National ID / Iqama" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="tel" id="t_phone" name="phone" placeholder="Phone Number" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                    <input type="email" id="t_email" name="email" placeholder="Email Address" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="t_host" name="host_name" placeholder="Host Name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                    <input type="text" id="t_purpose" name="purpose" placeholder="Purpose" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-xs transition-colors">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 block ml-1 uppercase">Arrival</label>
                        <input type="time" id="t_arr" name="arrival" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold text-xs border-2 border-transparent focus:border-blue-500 transition-colors">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 block ml-1 uppercase">Departure</label>
                        <input type="time" id="t_dep" name="departure" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold text-xs border-2 border-transparent focus:border-blue-500 transition-colors">
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 space-y-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 block">Vehicle Authorization</span>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="t_car" name="car_model" placeholder="Car Model" class="w-full p-3 bg-white dark:bg-slate-800 rounded-lg dark:text-white outline-none font-bold text-xs border border-transparent focus:border-blue-500 transition-colors">
                        <input type="text" id="t_plate" name="plate_number" placeholder="Plate Number" class="w-full p-3 bg-white dark:bg-slate-800 rounded-lg dark:text-white outline-none font-bold text-xs border border-transparent focus:border-blue-500 transition-colors uppercase">
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="fillTestData()" class="w-1/3 py-4 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black rounded-xl shadow-sm uppercase tracking-widest text-[10px] hover:bg-slate-300 dark:hover:bg-slate-700 transition-all">Test Fill</button>
                    <button type="submit" class="w-2/3 py-4 bg-blue-600 text-white font-black rounded-xl shadow-lg uppercase tracking-widest text-[10px] hover:bg-blue-700 hover:shadow-xl transition-all">Save Record</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleModal() { const m = document.getElementById('modal'); m.classList.toggle('opacity-0'); m.classList.toggle('pointer-events-none'); }
        function fillTestData() {
            document.getElementById('t_name').value = 'Majed Alotaibi'; document.getElementById('t_nid').value = '1012345678';
            document.getElementById('t_phone').value = '0501234567'; document.getElementById('t_email').value = 'ifaisalqz@gmail.com'; 
            document.getElementById('t_host').value = 'Abdullah'; document.getElementById('t_purpose').value = 'System Testing';
            document.getElementById('t_arr').value = '08:00'; document.getElementById('t_dep').value = '12:00';
            document.getElementById('t_car').value = 'Changan Eado'; document.getElementById('t_plate').value = 'KSA 999';
        }
    </script>
</body>
</html>