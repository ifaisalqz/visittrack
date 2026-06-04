<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B1120] min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <?php include 'includes/main_nav.php'; ?>

    <main class="flex-1 flex flex-col items-center justify-center p-6 w-full max-w-5xl mx-auto relative z-10">

        <div class="text-center space-y-6 mb-16 w-full">
            <h1 class="text-5xl md:text-7xl font-black tracking-tighter leading-[1.1] text-white">
                Smart & Secure <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400">Visitor Management</span>
            </h1>
            <p class="text-slate-400 text-lg font-medium max-w-xl mx-auto leading-relaxed">
                Experience seamless facility access. Request your visit, get approved, and use your digital QR pass for instant check-in.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-3xl">

            <a href="register.php" class="group relative bg-slate-900/80 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] border border-slate-800 hover:border-blue-500 transition-all duration-300 hover:-translate-y-2 overflow-hidden text-left block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2 relative z-10">New Visit Request</h3>
                <p class="text-slate-400 text-sm font-medium mb-8 relative z-10">Pre-register your visit details to receive an entry QR code.</p>
                <div class="flex items-center text-xs font-black uppercase tracking-widest text-blue-400 relative z-10 group-hover:translate-x-2 transition-transform">
                    Register Now
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </a>

            <a href="track.php" class="group relative bg-slate-900/80 backdrop-blur-xl p-8 md:p-10 rounded-[2.5rem] border border-slate-800 hover:border-slate-600 transition-all duration-300 hover:-translate-y-2 overflow-hidden text-left block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-500/5 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center mb-6 relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2 relative z-10">Track Status</h3>
                <p class="text-slate-400 text-sm font-medium mb-8 relative z-10">Check if your request is approved or rejected by the host.</p>
                <div class="flex items-center text-xs font-black uppercase tracking-widest text-white relative z-10 group-hover:translate-x-2 transition-transform">
                    Check Status
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
            </a>

        </div>
    </main>

    <?php include 'includes/main_footer.php'; ?>
</body>
</html>
