<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Password — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#070b14] text-slate-100 antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        @include('partials.sidebar')
        <main class="min-w-0 flex-1 p-6 lg:p-10">
            <div class="mx-auto max-w-5xl space-y-6">
                <header>
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-400">Admin</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-white">Request Reset Password</h1>
                    <p class="mt-1 text-sm text-slate-400">Tinjau identitas pemohon lalu kirim tautan reset ke email akun terdaftar.</p>
                </header>

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
                @endif

                <section class="space-y-4">
                    @forelse($requests as $resetRequest)
                        <article class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-white">{{ $resetRequest->name ?? 'Nama tidak ditemukan' }}</p>
                                    <p class="mt-1 break-all text-sm text-slate-400">{{ $resetRequest->email }}</p>
                                    <p class="mt-2 text-xs text-slate-500">Diminta {{ $resetRequest->created_at->format('d M Y, H:i') }}</p>
                                    @if($resetRequest->status === 'sent')
                                        <p class="mt-2 text-xs text-emerald-400">Link dikirim {{ $resetRequest->processed_at?->format('d M Y, H:i') }} oleh {{ $resetRequest->processedBy?->name ?? 'Admin' }}</p>
                                    @elseif(! $resetRequest->user || $resetRequest->user->isAdmin())
                                        <p class="mt-2 text-xs text-amber-300">Email tidak cocok dengan akun member yang dapat direset.</p>
                                    @endif
                                </div>
                                @if($resetRequest->status === 'pending' && $resetRequest->user && ! $resetRequest->user->isAdmin())
                                    <form action="{{ route('admin.password-requests.send', $resetRequest) }}" method="POST" onsubmit="return confirm('Kirim link reset ke {{ $resetRequest->email }}?');">
                                        @csrf
                                        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-xs font-bold text-white transition hover:bg-indigo-500 sm:w-auto">
                                            Setujui &amp; Kirim Link
                                        </button>
                                    </form>
                                @elseif($resetRequest->status === 'sent')
                                    <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">Terkirim</span>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-2xl border border-slate-800 bg-slate-900/70 p-8 text-center text-sm text-slate-400">Belum ada request reset password.</p>
                    @endforelse
                </section>

                @if($requests->hasPages())
                    <div>{{ $requests->links() }}</div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
