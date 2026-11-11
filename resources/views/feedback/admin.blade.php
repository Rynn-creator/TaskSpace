@extends('layouts.admin')

@section('title', 'Feedback Masuk')

@section('content')
    <header>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Masukan pengguna</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Feedback Masuk</h1>
        <p class="mt-2 text-sm text-slate-400">Tinjau pesan, kelompokkan berdasarkan status, dan balas pengguna.</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-3 sm:grid-cols-3">
        @foreach([['all', 'Semua feedback', 'text-white'], ['pending', 'Menunggu balasan', 'text-amber-300'], ['replied', 'Sudah dibalas', 'text-emerald-300']] as [$key, $label, $color])
            <a href="{{ route('admin.feedback.index', ['status' => $key]) }}" class="admin-surface admin-card rounded-2xl p-4 transition hover:-translate-y-0.5 hover:border-violet-400/30">
                <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-extrabold {{ $color }}">{{ $feedbackCounts[$key] }}</p>
            </a>
        @endforeach
    </section>

    <nav class="flex flex-wrap gap-2" aria-label="Filter status feedback">
        @foreach([['all', 'Semua'], ['pending', 'Menunggu balasan'], ['replied', 'Sudah dibalas']] as [$key, $label])
            <a href="{{ route('admin.feedback.index', ['status' => $key]) }}" class="rounded-full border px-3 py-1.5 text-xs font-bold transition {{ $status === $key ? 'border-violet-400/30 bg-violet-500/15 text-violet-200' : 'border-slate-800 bg-slate-900/50 text-slate-400 hover:text-white' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <section class="space-y-4">
        @forelse($feedbacks as $feedback)
            <article class="admin-surface admin-card space-y-4 rounded-2xl p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        @if($feedback->user)
                            @include('partials.profile-avatar', [
                                'profileUser' => $feedback->user,
                                'avatarClass' => 'flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-500/10 text-sm font-bold text-violet-200',
                            ])
                        @endif
                        <div class="min-w-0">
                            <p class="truncate font-bold text-white">{{ $feedback->user?->name ?? 'Pengguna dihapus' }} <span class="font-normal text-slate-500">({{ $feedback->user?->email ?? 'email tidak tersedia' }})</span></p>
                            <p class="mt-1 text-xs text-slate-500">Dikirim {{ $feedback->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $feedback->admin_reply ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-amber-500/20 bg-amber-500/10 text-amber-300' }}">{{ $feedback->admin_reply ? 'Sudah dibalas' : 'Menunggu balasan' }}</span>
                </div>
                <div class="rounded-xl border border-slate-800 bg-slate-950/45 p-4">
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Pesan feedback</p>
                    <p class="whitespace-pre-wrap break-words text-sm leading-6 text-slate-200">{{ $feedback->message }}</p>
                </div>
                @if($feedback->admin_reply)
                    <div class="rounded-xl border border-violet-500/15 bg-violet-500/5 p-4">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-violet-300">Balasan sebelumnya · {{ $feedback->replied_at?->format('d M Y, H:i') }}</p>
                        <p class="whitespace-pre-wrap break-words text-sm leading-6 text-slate-300">{{ $feedback->admin_reply }}</p>
                    </div>
                @endif
                @if($feedback->user)
                    <form action="{{ route('admin.feedback.reply', $feedback) }}" method="POST" class="space-y-3">
                        @csrf
                        <label for="reply-{{ $feedback->id }}" class="block text-sm font-bold text-slate-300">{{ $feedback->admin_reply ? 'Ubah balasan' : 'Balas feedback' }}</label>
                        <textarea id="reply-{{ $feedback->id }}" name="admin_reply" rows="3" maxlength="5000" required placeholder="Tulis balasan untuk pengguna..." class="w-full rounded-xl border border-slate-700 bg-slate-950/70 p-4 text-sm text-white placeholder-slate-500 outline-none transition focus:border-violet-400">{{ old('admin_reply') }}</textarea>
                        <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-violet-500">Kirim Balasan</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="admin-surface rounded-2xl p-8 text-center text-sm text-slate-400">Tidak ada feedback untuk status ini.</p>
        @endforelse
    </section>

    @if($feedbacks->hasPages())
        <div class="admin-surface rounded-xl px-5 py-3">{{ $feedbacks->links() }}</div>
    @endif
@endsection
