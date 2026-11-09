<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Pengguna — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#070b14] text-slate-100 antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        @include('partials.sidebar')
        <main class="min-w-0 flex-1 p-6 lg:p-10">
            <div class="mx-auto max-w-4xl space-y-6">
                <header>
                    <h1 class="text-2xl font-extrabold">Feedback Pengguna</h1>
                    <p class="mt-1 text-sm text-slate-400">Baca masukan user dan kirim balasan.</p>
        </header>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
        @endif

        <section class="space-y-4">
            @forelse($feedbacks as $feedback)
                <article class="space-y-4 rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-bold text-white">{{ $feedback->user->name }} <span class="font-normal text-slate-500">({{ $feedback->user->email }})</span></p>
                        <p class="text-xs text-slate-500">{{ $feedback->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <p class="whitespace-pre-wrap text-sm text-slate-200">{{ $feedback->message }}</p>

                    @if($feedback->admin_reply)
                        <div class="rounded-xl border border-slate-700 bg-slate-950/70 p-4">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-indigo-300">Balasan sebelumnya</p>
                            <p class="whitespace-pre-wrap text-sm text-slate-300">{{ $feedback->admin_reply }}</p>
                        </div>
                    @endif

                    <form action="{{ route('admin.feedback.reply', $feedback) }}" method="POST" class="space-y-3">
                        @csrf
                        <label for="reply-{{ $feedback->id }}" class="block text-sm font-bold text-slate-300">{{ $feedback->admin_reply ? 'Ubah balasan' : 'Balas feedback' }}</label>
                        <textarea id="reply-{{ $feedback->id }}" name="admin_reply" rows="3" maxlength="5000" required placeholder="Tulis balasan untuk user..." class="w-full rounded-xl border border-slate-700 bg-slate-950 p-4 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none">{{ old('admin_reply') }}</textarea>
                        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-500">Kirim Balasan</button>
                    </form>
                </article>
            @empty
                <p class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 text-sm text-slate-400">Belum ada feedback dari pengguna.</p>
            @endforelse
        </section>
            </div>
        </main>
    </div>
</body>
</html>
