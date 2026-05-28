<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../login.php"); exit(); }

// تضمين ملفات PHPMailer 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// مسار المكتبة
require '../vendor/autoload.php'; 

include '../includes/db.php';

// معالجة إضافة زائر من قبل الأدمن (Walk-in)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_visitor'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $purpose = htmlspecialchars($_POST['purpose']);
    $arrival = $_POST['arrival'];
    $departure = $_POST['departure'];
    
    $host_name = htmlspecialchars($_POST['host_name']);
    $national_id = htmlspecialchars($_POST['national_id']);
    $email = htmlspecialchars($_POST['email']);
    
    $car_model = trim($_POST['car_model']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_details = "";
    if(!empty($car_model) || !empty($plate_number)) {
        $vehicle_details = htmlspecialchars($car_model . " | " . strtoupper($plate_number));
    }
    
    $time1 = strtotime($arrival);
    $time2 = strtotime($departure);
    $duration_hours = round(($time2 - $time1) / 3600, 1);
    
    $tid = "ADM-" . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO visitors (full_name, phone, host_name, national_id, email, vehicle_details, purpose, duration_hours, arrival_time, departure_time, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $phone, $host_name, $national_id, $email, $vehicle_details, $purpose, $duration_hours, $arrival, $departure, $tid])) {
        
        // إيميل الـ Walk-in
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.office365.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@faisal.biz'; 
            $mail->Password   = 'bhwvxcvzsqmvnsgj';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('noreply@faisal.biz', 'Visit Track System');
            $mail->addAddress($email, $name); 
            
            $mail->isHTML(true);
            $mail->Subject = 'Walk-in Visitor Pass - Visit Track';
            
            $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; text-align: center; background-color: #f8fafc;">
                <h2 style="color: #2563eb;">Welcome to Visit Track!</h2>
                <p style="color: #64748b; font-size: 16px;">Dear <strong>' . $name . '</strong>,</p>
                <p style="color: #64748b; font-size: 16px;">Your walk-in visit has been registered successfully by our security desk. Please use the QR code below for your check-in/out.</p>
                
                <div style="background: white; padding: 15px; border-radius: 10px; display: inline-block; margin: 20px 0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $tid . '" alt="QR Code">
                </div>
                
                <p style="font-size: 14px; color: #94a3b8; text-transform: uppercase;">Tracking ID</p>
                <h3 style="color: #1e293b; font-size: 24px; letter-spacing: 2px; margin-top: 0;">' . $tid . '</h3>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px;">Planned Visit: ' . date('h:i A', strtotime($arrival)) . ' to ' . date('h:i A', strtotime($departure)) . '</p>
            </div>';

            $mail->send();
        } catch (Exception $e) {}
        
        header("Location: index.php"); 
        exit();
    }
}

// معالجة الأكشن (قبول/رفض) للطلبات المسجلة من الخارج
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'approve') {
        
        // جلب بيانات الزائر قبل التحديث لإرسال الإيميل له
        $v_stmt = $conn->prepare("SELECT full_name, email, tracking_id, arrival_time, departure_time FROM visitors WHERE id = ?");
        $v_stmt->execute([$id]);
        $visitor = $v_stmt->fetch();

        if ($visitor) {
            $conn->prepare("UPDATE visitors SET status = 'approved' WHERE id = ?")->execute([$id]);
            
            // إرسال إيميل الموافقة والقبول للزائر
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.office365.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'noreply@faisal.biz'; 
                $mail->Password   = 'bhwvxcvzsqmvnsgj'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('noreply@faisal.biz', 'Visit Track System');
                $mail->addAddress($visitor['email'], $visitor['full_name']); 
                
                $mail->isHTML(true);
                $mail->Subject = 'Your Visit Request Has Been Approved! - Visit Track';
                
                $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; text-align: center; background-color: #f8fafc;">
                    <h2 style="color: #16a34a;">Request Approved!</h2>
                    <p style="color: #64748b; font-size: 16px;">Dear <strong>' . htmlspecialchars($visitor['full_name']) . '</strong>,</p>
                    <p style="color: #64748b; font-size: 16px;">Good news! Your visit request has been approved by the administration. You can now use the QR code below at the gate.</p>
                    
                    <div style="background: white; padding: 15px; border-radius: 10px; display: inline-block; margin: 20px 0;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $visitor['tracking_id'] . '" alt="QR Code">
                    </div>
                    
                    <p style="font-size: 14px; color: #94a3b8; text-transform: uppercase;">Tracking ID</p>
                    <h3 style="color: #1e293b; font-size: 24px; letter-spacing: 2px; margin-top: 0;">' . $visitor['tracking_id'] . '</h3>
                    
                    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                    <p style="color: #94a3b8; font-size: 12px;">Approved Time: ' . date('h:i A', strtotime($visitor['arrival_time'])) . ' to ' . date('h:i A', strtotime($visitor['departure_time'])) . '</p>
                </div>';

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
    <title>Management - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } .font-style-normal { font-style: normal !important; }</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 flex min-h-screen transition-colors duration-300">
    
<?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-12 overflow-x-hidden">
        <header class="mb-12 flex justify-between items-center text-slate-800 dark:text-white">
            <div>
                <h1 class="text-4xl font-black tracking-tight font-style-normal italic">Active Management</h1>
                <p class="text-slate-400 mt-2 font-medium uppercase text-[10px] tracking-[0.2em] font-style-normal">Live visitor monitoring</p>
            </div>
            <button onclick="toggleModal()" class="px-6 py-4 bg-slate-800 dark:bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-blue-600 transition-all font-style-normal">+ Add Walk-in</button>
        </header>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 uppercase text-[10px] font-black tracking-widest text-slate-400">
                        <th class="p-6 w-1/3">Visitor Details</th>
                        <th class="p-6">Destination & Purpose</th>
                        <th class="p-6">Vehicle Access</th>
                        <th class="p-6">Planned Time</th>
                        <th class="p-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <?php foreach($active as $r): ?>
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="p-6">
                            <div class="font-black text-slate-800 dark:text-slate-100 text-lg tracking-tighter font-style-normal"><?php echo htmlspecialchars($r['full_name']); ?></div>
                            <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">ID: <?php echo htmlspecialchars($r['national_id']); ?> | Mob: <?php echo htmlspecialchars($r['phone']); ?></div>
                            <div class="text-[10px] text-slate-400 font-medium lowercase"><?php echo htmlspecialchars($r['email']); ?></div>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300 font-style-normal">Visiting: <span class="text-blue-600 dark:text-blue-400 font-black"><?php echo htmlspecialchars($r['host_name']); ?></span></div>
                            <div class="text-[11px] font-bold text-slate-400 mt-0.5 uppercase tracking-wider">Reason: <?php echo htmlspecialchars($r['purpose']); ?></div>
                        </td>
                        <td class="p-6">
                            <?php if(!empty($r['vehicle_details'])): ?>
                                <span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 block text-center max-w-[200px] font-mono"><?php echo htmlspecialchars($r['vehicle_details']); ?></span>
                            <?php else: ?>
                                <span class="text-slate-300 dark:text-slate-600 text-xs italic">No Vehicle</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-6">
                            <div class="text-sm font-black text-blue-600 dark:text-blue-400 uppercase font-style-normal italic"><?php echo date('h:i A', strtotime($r['arrival_time'])); ?> - <?php echo date('h:i A', strtotime($r['departure_time'])); ?></div>
                        </td>
                        <td class="p-6 flex justify-center gap-3 items-center min-h-[90px]">
                            <a href="?action=approve&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase shadow-lg font-style-normal tracking-widest hover:bg-blue-700 transition">Approve</a>
                            <a href="?action=rejected&id=<?php echo $r['id']; ?>" class="px-5 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-red-500 rounded-xl text-[10px] font-black uppercase font-style-normal hover:bg-red-50 dark:hover:bg-red-950/20 transition">Reject</a>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($active)) echo "<tr><td colspan='5' class='p-10 text-center text-slate-400 italic font-medium'>No pending requests found</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 transition-all">
        <div class="modal-overlay absolute w-full h-full bg-slate-900 opacity-60 backdrop-blur-sm"></div>
        <div class="modal-container bg-white dark:bg-slate-900 w-full max-w-xl mx-auto rounded-[2.5rem] shadow-2xl z-50 border border-slate-100 dark:border-slate-800 p-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-black uppercase text-slate-800 dark:text-white mb-6 italic tracking-tighter font-style-normal">Add Walk-in Visitor</h3>
            <form action="index.php" method="POST" class="space-y-4">
                <input type="hidden" name="add_visitor" value="1">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="t_name" name="name" placeholder="Full Name" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                    <input type="text" id="t_nid" name="national_id" placeholder="National ID / Iqama" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="tel" id="t_phone" name="phone" placeholder="Phone Number" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                    <input type="email" id="t_email" name="email" placeholder="Email Address" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="t_host" name="host_name" placeholder="Host Name (Visiting Who?)" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                    <input type="text" id="t_purpose" name="purpose" placeholder="Visit Purpose" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold border-2 border-transparent focus:border-blue-500 text-sm">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 block ml-1">Arrival</label>
                        <input type="time" id="t_arr" name="arrival" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 block ml-1">Departure</label>
                        <input type="time" id="t_dep" name="departure" required class="w-full p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl dark:text-white outline-none font-bold text-sm">
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-3">
                    <span class="text-[10px] font-black uppercase text-blue-600 block">Vehicle Authorization (Optional)</span>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="t_car" name="car_model" placeholder="Car Make & Model" class="w-full p-3 bg-white dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold text-xs shadow-sm">
                        <input type="text" id="t_plate" name="plate_number" placeholder="Plate Number" class="w-full p-3 bg-white dark:bg-slate-800 rounded-xl dark:text-white outline-none font-bold text-xs shadow-sm uppercase">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="fillTestData()" class="w-1/3 py-5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-black rounded-2xl shadow-sm uppercase tracking-widest text-xs font-style-normal hover:bg-slate-300 dark:hover:bg-slate-700 transition">Test Fill</button>
                    <button type="submit" class="w-2/3 py-5 bg-blue-600 text-white font-black rounded-2xl shadow-xl uppercase tracking-widest text-xs font-style-normal hover:bg-blue-700 transition">Save Record</button>
                </div>
            </form>
            <button onclick="toggleModal()" class="mt-4 text-slate-400 w-full font-bold uppercase text-[10px] tracking-widest font-style-normal">Close Window</button>
        </div>
    </div>
    
    <script>
        function toggleModal() {
            const m = document.getElementById('modal');
            m.classList.toggle('opacity-0'); m.classList.toggle('pointer-events-none');
        }

        function fillTestData() {
            document.getElementById('t_name').value = 'Majed Alotaibi';
            document.getElementById('t_nid').value = '1012345678';
            document.getElementById('t_phone').value = '0501234567';
            document.getElementById('t_email').value = 'ifaisalqz@gmail.com'; 
            document.getElementById('t_host').value = 'Abdullah';
            document.getElementById('t_purpose').value = 'System Deployment & Testing';
            document.getElementById('t_arr').value = '08:00';
            document.getElementById('t_dep').value = '12:00';
            document.getElementById('t_car').value = '2024 Changan Eado Plus';
            document.getElementById('t_plate').value = 'KSA 9999';
        }
    </script>
</body>
</html>