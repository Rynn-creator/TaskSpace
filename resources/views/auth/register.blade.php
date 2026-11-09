<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#070b14] text-slate-100 min-h-screen flex antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Left Hero Banner Section -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-indigo-950 via-slate-900 to-[#070b14] p-12 flex-col justify-between overflow-hidden border-r border-slate-800/80">
        
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-indigo-600/30 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-violet-600/20 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-violet-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <span class="font-extrabold text-white tracking-tight text-xl">TaskSpace</span>
        </div>

        <div class="relative z-10 my-auto max-w-lg">
            <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20">Create Account</span>
            <h1 class="text-4xl font-extrabold text-white tracking-tight leading-tight mt-6 mb-4">
                Bergabung & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Mulai Kelola Task</span> Bebas Hambatan.
            </h1>
            <p class="text-slate-400 text-sm leading-relaxed">
                Buat ruang kerja pribadi kamu sendiri. Seluruh data terenkripsi dan terisolasi khusus untuk akun kamu.
            </p>
        </div>

        <p class="text-xs text-slate-500 relative z-10">&copy; 2026 TaskSpace Inc. Built with Laravel 12.</p>
    </div>

    <!-- Right Form Register Section -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 relative">
        <div class="w-full max-w-md space-y-6">
            
            <div class="flex lg:hidden items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="font-extrabold text-white text-xl">TaskSpace</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400">Get Started</span>
                <h2 class="text-3xl font-extrabold text-white tracking-tight mt-1">Buat Akun Baru</h2>
                <p class="text-xs text-slate-400 mt-1">Isi formulir di bawah untuk mendaftarkan akun kamu.</p>
            </div>

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="Nama kamu" 
                        value="{{ old('name') }}"
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="nama@email.com" 
                        value="{{ old('email') }}"
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Minimal 6 karakter" 
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        placeholder="Ketik ulang password" 
                        required
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-sm py-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all active:scale-95 flex items-center justify-center gap-2 mt-2"
                >
                    <span>Daftar Akun Baru</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 pt-2">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="text-indigo-400 font-bold hover:underline">Masuk di sini</a>
            </p>

        </div>
    </div>

</body>
</html>