<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#070b14] text-slate-100 antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        @include('partials.sidebar')
        <main class="min-w-0 flex-1 p-6 lg:p-10">
            <div class="mx-auto max-w-3xl space-y-6">
                <header>
                    <h1 class="text-2xl font-extrabold">Feedback</h1>
                    <p class="mt-1 text-sm text-slate-400">Kirim saran atau laporkan masalah. Admin akan membalas di halaman ini.</p>
        </header>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('feedback.store') }}" method="POST" class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
            @csrf
            <label for="message" class="block text-sm font-bold text-slate-300">Pesan feedback</label>
            <textarea id="message" name="message" rows="5" maxlength="5000" required placeholder="Tulis feedback atau saran kamu..." class="w-full rounded-xl border border-slate-700 bg-slate-950 p-4 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none">{{ old('message') }}</textarea>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-500">Kirim Feedback</button>
        </form>

        <section class="space-y-4">
            <h2 class="text-lg font-bold">Feedback saya</h2>
            @forelse($feedbacks as $feedback)
                <article class="space-y-3 rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <p class="whitespace-pre-wrap text-sm text-slate-200">{{ $feedback->message }}</p>
                    <p class="text-xs text-slate-500">{{ $feedback->created_at->format('d M Y, H:i') }}</p>
                    @if($feedback->admin_reply)
                        <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-4">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-indigo-300">Balasan Admin</p>
                            <p class="whitespace-pre-wrap text-sm text-slate-200">{{ $feedback->admin_reply }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $feedback->replied_at?->format('d M Y, H:i') }}</p>
                        </div>
                    @else
                        <p class="text-xs font-semibold text-amber-300">Menunggu balasan admin</p>
                    @endif
                </article>
            @empty
                <p class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 text-sm text-slate-400">Belum ada feedback yang dikirim.</p>
            @endforelse
        </section>
            </div>
        </main>
    </div>
</body>
</html>
