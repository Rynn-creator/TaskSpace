<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-[#070b14] p-6 text-slate-100 antialiased">
    <main class="w-full max-w-md space-y-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-2xl">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-400">Reset Password</p>
            <h1 class="mt-2 text-2xl font-extrabold text-white">Buat Password Baru</h1>
            <p class="mt-2 text-sm text-slate-400">Tautan ini hanya berlaku dalam waktu terbatas.</p>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Email akun</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label for="password" class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Password baru</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Konfirmasi password baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">
                Simpan Password Baru
            </button>
        </form>
    </main>
</body>
</html>
