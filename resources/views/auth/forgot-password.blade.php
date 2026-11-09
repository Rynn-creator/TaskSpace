<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Reset Password — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-[#070b14] p-6 text-slate-100 antialiased">
    <main class="w-full max-w-md space-y-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-2xl">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-400">Bantuan Akun</p>
            <h1 class="mt-2 text-2xl font-extrabold text-white">Lupa Password?</h1>
            <p class="mt-2 text-sm text-slate-400">Masukkan email akunmu. Admin akan meninjau permintaan dan mengirim link reset jika disetujui.</p>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.request.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-xs font-bold uppercase tracking-widest text-slate-400">Email akun</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                    class="w-full rounded-xl border border-slate-800 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
            </div>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-500">
                Kirim Permintaan ke Admin
            </button>
        </form>

        <a href="{{ route('login') }}" class="block text-center text-xs font-semibold text-slate-400 hover:text-white">Kembali ke login</a>
    </main>
</body>
</html>
