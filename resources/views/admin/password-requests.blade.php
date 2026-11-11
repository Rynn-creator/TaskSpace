@extends('layouts.admin')

@section('title', 'Request Reset Password')

@section('content')
    <header>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Permintaan akun</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Request Reset Password</h1>
        <p class="mt-2 text-sm text-slate-400">Tinjau identitas pemohon lalu kirim tautan reset ke email akun terdaftar.</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="space-y-4">
        @forelse($requests as $resetRequest)
            <article class="admin-surface admin-card rounded-2xl p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="truncate font-bold text-white">{{ $resetRequest->name ?? 'Nama tidak ditemukan' }}</p>
                        <p class="mt-1 break-all text-sm text-slate-400">{{ $resetRequest->email }}</p>
                        <p class="mt-2 text-xs text-slate-500">Diminta {{ $resetRequest->created_at->format('d M Y, H:i') }}</p>
                        @if($resetRequest->status === 'sent')
                            <p class="mt-2 text-xs text-emerald-400">Link dikirim {{ $resetRequest->processed_at?->format('d M Y, H:i') }} oleh {{ $resetRequest->processedBy?->name ?? 'Admin' }}</p>
                        @elseif(! $resetRequest->user || $resetRequest->user->isAdmin())
                            <p class="mt-2 text-xs text-amber-300">Email tidak cocok dengan akun pengguna yang dapat direset.</p>
                        @endif
                    </div>
                    @if($resetRequest->status === 'pending' && $resetRequest->user && ! $resetRequest->user->isAdmin())
                        <form action="{{ route('admin.password-requests.send', $resetRequest) }}" method="POST" onsubmit="return confirm('Kirim tautan reset ke alamat email pemohon?')">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-violet-600 px-4 py-3 text-xs font-bold text-white transition hover:bg-violet-500 sm:w-auto">Setujui &amp; Kirim Link</button>
                        </form>
                    @elseif($resetRequest->status === 'sent')
                        <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">Terkirim</span>
                    @endif
                </div>
            </article>
        @empty
            <p class="admin-surface rounded-2xl p-8 text-center text-sm text-slate-400">Belum ada request reset password.</p>
        @endforelse
    </section>

    @if($requests->hasPages())
        <div class="admin-surface rounded-xl px-5 py-3">{{ $requests->links() }}</div>
    @endif
@endsection
