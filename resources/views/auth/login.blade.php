<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Keyframes Animasi yang Diperjelas */
        @keyframes floatOrb {
            0%, 100% { transform: translateY(0px) scale(1); opacity: 0.35; }
            50% { transform: translateY(-35px) scale(1.15); opacity: 0.7; }
        }
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }
            50% { box-shadow: 0 0 30px rgba(99, 102, 241, 0.5); }
        }

        .animate-orb-1 { animation: floatOrb 6s infinite ease-in-out; }
        .animate-orb-2 { animation: floatOrb 8s infinite ease-in-out 3s; }
        .animate-card-entry { animation: slideUpFade 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        /* Staggered Delay untuk Elemen Masuk */
        .delay-1 { animation-delay: 100ms; opacity: 0; }
        .delay-2 { animation-delay: 200ms; opacity: 0; }
        .delay-3 { animation-delay: 300ms; opacity: 0; }
        .delay-4 { animation-delay: 400ms; opacity: 0; }
    </style>
</head>
<body class="bg-[#070b14] text-slate-100 min-h-screen flex antialiased selection:bg-indigo-500 selection:text-white overflow-hidden">

    <!-- Left Hero Banner Section (With Dynamic Floating Glow) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-indigo-950 via-slate-900 to-[#070b14] p-12 flex-col justify-between overflow-hidden border-r border-slate-800/80">
        
        <!-- Interactive Glowing Orbs Background -->
        <div class="absolute -top-24 -left-24 w-[28rem] h-[28rem] bg-indigo-600/35 rounded-full blur-[90px] pointer-events-none animate-orb-1"></div>
        <div class="absolute -bottom-24 -right-24 w-[28rem] h-[28rem] bg-violet-600/25 rounded-full blur-[90px] pointer-events-none animate-orb-2"></div>

        <!-- Logo Header Section -->
        <div class="flex items-center gap-3 relative z-10 animate-card-entry delay-1">
            <div class="w-11 h-11 bg-gradient-to-tr from-indigo-600 to-violet-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 transform hover:rotate-12 hover:scale-110 transition-all duration-300 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="font-extrabold text-white tracking-tight text-xl">TaskSpace</span>
        </div>

        <!-- Headline Content (Staggered Entrance Animation) -->
        <div class="relative z-10 my-auto max-w-lg">
            <span class="inline-block text-xs font-bold text-indigo-400 uppercase tracking-widest px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 mb-6 animate-card-entry delay-2">
                Workplace & Task Manager
            </span>
            
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4 animate-card-entry delay-3">
                Kelola Setiap Target & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-violet-400 to-pink-400">Proyek Harianmu</span> Lebih Terstruktur.
            </h1>
            
            <p class="text-slate-400 text-sm leading-relaxed animate-card-entry delay-4">
                Pantau statistik progres, atur skala prioritas, dan selesaikan tugas-tugas penting dalam satu dashboard minimalis yang terintegrasi.
            </p>
        </div>

        <!-- Footer Copyright -->
        <p class="text-xs text-slate-500 relative z-10 animate-card-entry delay-4">&copy; 2026 TaskSpace Inc. Created for Vocational High School Certification.</p>
    </div>

    <!-- Right Form Login Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 relative">
        <div class="w-full max-w-md space-y-8 animate-card-entry delay-2">
            
            <!-- Mobile Header Logo -->
            <div class="flex lg:hidden items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="font-extrabold text-white text-xl">TaskSpace</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400">Welcome Back</span>
                <h2 class="text-3xl font-extrabold text-white tracking-tight mt-1">Selamat Datang Kembali!</h2>
                <p class="text-xs text-slate-400 mt-1">Silakan masuk ke akun kamu untuk melanjutkan dashboard.</p>
            </div>

            <!-- Error Validation Message -->
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold animate-card-entry">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('status'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div class="group">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-400 transition-colors">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="nama@email.com" 
                        value="{{ old('email') }}"
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:scale-[1.01] transition-all duration-300"
                    >
                </div>

                <div class="group">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-400 transition-colors">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Masukkan password" 
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:scale-[1.01] transition-all duration-300"
                    >
                </div>

                <div class="text-right">
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 hover:underline">Lupa password?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-sm py-4 rounded-xl shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center justify-center gap-2 group"
                >
                    <span>Masuk ke Akun</span>
                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 pt-4">
                Belum memiliki akun? 
                <a href="{{ route('register') }}" class="text-indigo-400 font-bold hover:text-indigo-300 hover:underline transition-colors">Daftar sekarang</a>
            </p>

        </div>
    </div>

</body>
</html>