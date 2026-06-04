<?php
session_start();
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: admin/index.php");
    exit();
}

include 'includes/db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin']    = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'] ?? 'supervisor';
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
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — VisitTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B1120] flex items-center justify-center min-h-screen p-6">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <div class="max-w-md w-full bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl p-10 border border-slate-800">

        <div class="text-center mb-10">
            <div class="inline-block p-5 bg-blue-500/10 rounded-3xl mb-4">
                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tighter uppercase">Admin Portal</h1>
            <p class="text-slate-400 mt-2 text-sm font-medium tracking-wide uppercase">Secure Login Access</p>
        </div>

        <?php if($error): ?>
        <div class="bg-red-500/10 border-l-4 border-red-500 text-red-400 p-4 rounded-xl mb-6 text-sm">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1 tracking-widest">Username</label>
                <input type="text" name="username" required autocomplete="off"
                    class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm shadow-sm"
                    placeholder="Enter username">
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-1 tracking-widest">Password</label>
                <input type="password" name="password" required
                    class="w-full p-4 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm shadow-sm"
                    placeholder="••••••••">
            </div>
            <button type="submit" class="w-full py-5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl transition-all active:scale-[0.98] uppercase tracking-widest text-xs">
                Sign In
            </button>
        </form>

        <div class="mt-10 text-center">
            <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 text-slate-400 hover:text-blue-400 rounded-xl transition-all font-bold text-xs uppercase tracking-widest border border-transparent">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
