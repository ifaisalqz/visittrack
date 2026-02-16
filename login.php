<?php
// ابدأ الجلسة في أول السطر
session_start();

// إذا كان الأدمن مسجل دخوله بالفعل، انقله مباشرة للوحة التحكم
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: admin/index.php");
    exit();
}

include 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تنظيف المدخلات لمنع الثغرات
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // البحث عن المستخدم
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // التحقق من صحة البيانات
        // ملاحظة احترافية: admin123 هو الباسورد الافتراضي في الداتا بيز
        if ($user && $password === $user['password']) {
            // تخزين بيانات الجلسة بشكل صحيح
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_user'] = $user['username'];
            
            // التوجه للوحة التحكم
            header("Location: admin/index.php");
            exit();
        } else {
            $error = "Incorrect username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* إضافة لمسة احترافية للخلفية */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-10 border border-gray-100 transition-all">
        <div class="text-center mb-10">
            <div class="inline-block p-4 bg-blue-50 rounded-full mb-4">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter">ADMIN PORTAL</h1>
            <p class="text-gray-400 mt-2 text-sm font-medium">Secure Access for Visit Track</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 text-sm flex items-center animate-bounce">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-2 ml-1">Username</label>
                <input type="text" name="username" required autocomplete="off"
                class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 ring-blue-500 focus:bg-white transition-all placeholder-slate-300" placeholder="admin">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-2 ml-1">Password</label>
                <input type="password" name="password" required 
                class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:ring-2 ring-blue-500 focus:bg-white transition-all placeholder-slate-300" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 transform transition active:scale-95">
                Sign In
            </button>
        </form>
        
        <div class="mt-10 text-center">
            <a href="index.php" class="text-sm text-slate-400 hover:text-blue-600 transition font-medium flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Public Registration
            </a>
        </div>
    </div>

</body>
</html>