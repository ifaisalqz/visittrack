<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visit Track - Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="p-6 bg-white sticky top-0 z-50 flex justify-between items-center px-12 border-b border-slate-100">
        <h1 class="text-2xl font-black text-blue-600 tracking-tighter italic">VISIT TRACK</h1>
        <a href="login.php" class="px-5 py-2 bg-blue-50 text-blue-600 rounded-xl font-bold text-sm hover:bg-blue-600 hover:text-white transition-all">Admin Portal</a>
    </nav>

    <div class="max-w-3xl mx-auto my-16 p-10 bg-white rounded-[2.5rem] shadow-2xl shadow-blue-100 border border-slate-50">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold tracking-tight">Visitor Registration</h2>
            <p class="text-slate-400 mt-2 font-medium italic">Official Hours: 07:00 AM - 04:00 PM</p>
        </div>
        
        <form action="submit_request.php" method="POST" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase ml-1">Full Name</label>
                    <input type="text" name="name" required class="w-full p-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 ring-blue-500 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase ml-1">Phone Number</label>
                    <input type="tel" name="phone" required class="w-full p-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 ring-blue-500 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-y border-slate-50 py-8">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase ml-1 text-blue-600">Arrival Time</label>
                    <input type="time" name="arrival" min="07:00" max="16:00" required class="w-full p-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 ring-blue-500 font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-500 uppercase ml-1 text-blue-600">Departure Time</label>
                    <input type="time" name="departure" min="07:00" max="16:00" required class="w-full p-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 ring-blue-500 font-bold">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-500 uppercase ml-1">Purpose of Visit</label>
                <textarea name="purpose" rows="3" required class="w-full p-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 ring-blue-500 transition-all"></textarea>
            </div>

            <button type="submit" class="w-full py-5 bg-blue-600 text-white font-black rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-700 transition active:scale-[0.98] text-lg uppercase tracking-widest">
                Register Visit
            </button>
        </form>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            Swal.fire({
                title: 'Success!',
                text: 'Your request has been sent. Your ID: ' + urlParams.get('tid'),
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'Track Status'
            }).then(() => {
                window.location.href = 'visitor_status.php?tid=' + urlParams.get('tid');
            });
        }
    </script>
</body>
</html>