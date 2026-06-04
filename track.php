<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Request — Visit Track</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#0B1120] min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">

    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none -z-10"></div>

    <?php include 'includes/main_nav.php'; ?>

    <main class="flex-1 flex items-center justify-center p-6 relative z-10">
        <div class="w-full max-w-5xl mx-auto flex items-center justify-center">
        <div class="max-w-md w-full text-center space-y-8">

            <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-900/80 backdrop-blur-xl rounded-[2rem] shadow-sm border border-slate-800">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div>
                <h1 class="text-4xl font-black text-white tracking-tighter mb-3">Track Status</h1>
                <p class="text-slate-400 text-sm font-medium leading-relaxed">Enter your Tracking ID to view your digital pass.</p>
            </div>

            <form action="visitor_status.php" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <input type="text" name="tid" required autocomplete="off" placeholder="VST-XXXXX"
                    class="block w-full pl-14 pr-32 py-5 bg-slate-900/80 backdrop-blur-xl border-2 border-slate-800 rounded-[2rem] outline-none transition-all focus:border-blue-500 text-white font-black uppercase tracking-widest text-sm placeholder:normal-case placeholder:font-medium placeholder:tracking-normal">
                <div class="absolute inset-y-2 right-2">
                    <button type="submit" class="h-full px-6 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl shadow-md transition-all active:scale-[0.95] text-xs uppercase tracking-widest">
                        Track
                    </button>
                </div>
            </form>

        </div>
    </main>
</body>
</html>
