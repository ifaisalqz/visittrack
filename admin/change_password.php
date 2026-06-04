<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit();
}
include '../includes/db.php';

$success = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (password_verify($new, $user['password'])) {
        $error = "New password must be different from your current password.";
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $conn->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['admin_id']]);
        $success = "Password updated successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password — VisitTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B1120] min-h-screen flex">

    <?php include '../includes/admin_nav.php'; ?>

    <main class="flex-1 p-8 md:p-12 overflow-auto">
        <div class="max-w-lg mx-auto">

            <div class="mb-10">
                <h1 class="text-4xl font-black text-white tracking-tighter">Change Password</h1>
                <p class="text-slate-400 mt-2 uppercase text-[10px] font-black tracking-[0.2em]">Update your credentials</p>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-2xl rounded-[2.5rem] border border-slate-800/80 shadow-xl p-8 md:p-10">

                <?php if ($success): ?>
                <div class="mb-6 flex items-center gap-4 bg-green-500/10 border border-green-500/30 rounded-2xl px-5 py-4">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-black text-green-400"><?php echo htmlspecialchars($success); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="mb-6 flex items-center gap-4 bg-red-500/10 border border-red-500/30 rounded-2xl px-5 py-4">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-black text-red-400"><?php echo htmlspecialchars($error); ?></p>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5" onsubmit="return validateForm()">

                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Current Password</label>
                        <div class="relative">
                            <input type="password" name="current_password" id="currentPwd" required
                                   class="w-full p-4 pr-12 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm transition-all">
                            <button type="button" onclick="togglePwd('currentPwd', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                                <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-slate-800 pt-5">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">New Password</p>

                        <div class="mb-4">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">New Password</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPwd" required minlength="8"
                                       oninput="checkStrength(this.value)"
                                       class="w-full p-4 pr-12 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm transition-all">
                                <button type="button" onclick="togglePwd('newPwd', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <div class="mt-2 flex gap-1">
                                <div id="bar1" class="h-1 flex-1 rounded-full bg-slate-700 transition-colors duration-300"></div>
                                <div id="bar2" class="h-1 flex-1 rounded-full bg-slate-700 transition-colors duration-300"></div>
                                <div id="bar3" class="h-1 flex-1 rounded-full bg-slate-700 transition-colors duration-300"></div>
                                <div id="bar4" class="h-1 flex-1 rounded-full bg-slate-700 transition-colors duration-300"></div>
                            </div>
                            <p id="strengthLabel" class="text-[10px] font-black mt-1 ml-1 text-slate-400 uppercase tracking-widest"></p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-2 ml-1">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirmPwd" required
                                       oninput="checkMatch()"
                                       class="w-full p-4 pr-12 bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none text-white font-bold text-sm transition-all">
                                <button type="button" onclick="togglePwd('confirmPwd', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <p id="matchLabel" class="text-[10px] font-black mt-1 ml-1 uppercase tracking-widest hidden"></p>
                        </div>
                    </div>

                    <div class="bg-slate-800/50 rounded-2xl p-4 space-y-2">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-3">Requirements</p>
                        <div class="flex items-center gap-2" id="req-len"><div class="w-3 h-3 rounded-full bg-slate-600 flex-shrink-0 transition-colors"></div><span class="text-xs font-bold text-slate-400">At least 8 characters</span></div>
                        <div class="flex items-center gap-2" id="req-upper"><div class="w-3 h-3 rounded-full bg-slate-600 flex-shrink-0 transition-colors"></div><span class="text-xs font-bold text-slate-400">One uppercase letter (A–Z)</span></div>
                        <div class="flex items-center gap-2" id="req-num"><div class="w-3 h-3 rounded-full bg-slate-600 flex-shrink-0 transition-colors"></div><span class="text-xs font-bold text-slate-400">One number (0–9)</span></div>
                        <div class="flex items-center gap-2" id="req-sym"><div class="w-3 h-3 rounded-full bg-slate-600 flex-shrink-0 transition-colors"></div><span class="text-xs font-bold text-slate-400">One special character (!@#$…)</span></div>
                    </div>

                    <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl uppercase tracking-widest text-xs hover:bg-blue-700 active:scale-[0.98] transition-all shadow-lg shadow-blue-500/20">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
    function togglePwd(id, btn) {
        const input = document.getElementById(id);
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.querySelector('.eye-icon').innerHTML = isText
            ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
            : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
    }
    function checkStrength(val) {
        const checks = { len: val.length >= 8, upper: /[A-Z]/.test(val), num: /[0-9]/.test(val), sym: /[^A-Za-z0-9]/.test(val) };
        setReq('req-len', checks.len); setReq('req-upper', checks.upper); setReq('req-num', checks.num); setReq('req-sym', checks.sym);
        const score = Object.values(checks).filter(Boolean).length;
        const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
        const labels = ['','Weak','Fair','Good','Strong'];
        ['bar1','bar2','bar3','bar4'].forEach((b,i) => { document.getElementById(b).className = 'h-1 flex-1 rounded-full transition-colors duration-300 ' + (i < score ? colors[score-1] : 'bg-slate-700'); });
        document.getElementById('strengthLabel').textContent = labels[score] || '';
        document.getElementById('strengthLabel').className = 'text-[10px] font-black mt-1 ml-1 uppercase tracking-widest ' + ['text-slate-400','text-red-500','text-orange-500','text-yellow-500','text-green-500'][score];
        checkMatch();
    }
    function setReq(id, pass) {
        document.querySelector('#' + id + ' div').className = 'w-3 h-3 rounded-full flex-shrink-0 transition-colors ' + (pass ? 'bg-green-500' : 'bg-slate-600');
        document.querySelector('#' + id + ' span').className = 'text-xs font-bold transition-colors ' + (pass ? 'text-green-400' : 'text-slate-400');
    }
    function checkMatch() {
        const newVal = document.getElementById('newPwd').value;
        const confVal = document.getElementById('confirmPwd').value;
        const label = document.getElementById('matchLabel');
        if (!confVal) { label.classList.add('hidden'); return; }
        label.classList.remove('hidden');
        label.textContent = newVal === confVal ? 'Passwords match' : 'Passwords do not match';
        label.className = 'text-[10px] font-black mt-1 ml-1 uppercase tracking-widest ' + (newVal === confVal ? 'text-green-500' : 'text-red-500');
    }
    function validateForm() {
        if (document.getElementById('newPwd').value !== document.getElementById('confirmPwd').value) {
            document.getElementById('confirmPwd').focus(); return false;
        }
        return true;
    }
    </script>
</body>
</html>
