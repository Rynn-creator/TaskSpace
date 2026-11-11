@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    <div data-live-page="dashboard" data-live-url="{{ route('admin.live.dashboard') }}" class="space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Ringkasan aplikasi</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Dashboard Admin</h1>
            <p class="mt-2 text-sm text-slate-400">Gambaran singkat kondisi pengguna, tugas, dan aktivitas terbaru.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="text-right">
                <p class="flex items-center justify-end gap-2 text-xs font-bold text-emerald-300"><span data-live-dot class="h-2 w-2 rounded-full bg-slate-500"></span><span data-live-status>Memulai...</span></p>
                <p class="mt-1 text-[10px] text-slate-500">Terakhir diperbarui <time data-live-updated>—</time></p>
            </div>
            <button type="button" data-live-refresh class="rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2.5 text-xs font-bold text-slate-300 transition hover:border-violet-400/40 hover:text-white">Refresh</button>
            <a href="{{ route('admin.statistics') }}" class="inline-flex items-center justify-center rounded-xl border border-violet-400/20 bg-violet-500/10 px-4 py-2.5 text-sm font-bold text-violet-200 transition hover:bg-violet-500/20">Lihat statistik <span class="ml-2" aria-hidden="true">→</span></a>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan statistik">
        @foreach([
            ['Total pengguna', $totalUsers, 'Akun dengan role pengguna', 'text-blue-300', 'totalUsers'],
            ['Pengguna aktif', $activeUsers, 'Akun yang dapat masuk', 'text-emerald-300', 'activeUsers'],
            ['Total tugas', $totalTasks, 'Seluruh tugas di aplikasi', 'text-violet-300', 'totalTasks'],
            ['Pengguna online', $onlineUsers, 'Aktif dalam 5 menit terakhir', 'text-cyan-300', 'onlineUsers'],
            ['Tugas selesai', $completedTasks, 'Tugas berstatus selesai', 'text-emerald-300', 'completedTasks'],
            ['Belum selesai', $pendingTasks, 'Tugas yang masih berjalan', 'text-amber-300', 'pendingTasks'],
            ['Terlambat', $overdueTasks, 'Belum selesai dan lewat tenggat', 'text-rose-300', 'overdueTasks'],
        ] as $index => [$label, $value, $hint, $color, $key])
            <article class="admin-surface admin-card rounded-2xl p-5 transition duration-200 hover:-translate-y-1 hover:border-violet-400/30" style="animation-delay: {{ $index * 45 }}ms">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p data-stat="{{ $key }}" class="mt-3 text-3xl font-extrabold {{ $color }}">{{ number_format($value) }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>
            </article>
        @endforeach
        <article class="admin-surface admin-card rounded-2xl p-5 transition duration-200 hover:-translate-y-1 hover:border-violet-400/30" style="animation-delay: 315ms">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kondisi sistem</p>
            <p class="mt-3 flex items-center gap-2 text-lg font-bold text-emerald-300"><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>Beroperasi</p>
            <p class="mt-2 text-xs text-slate-500">Database aktif; dashboard berhasil mengambil data terkini.</p>
        </article>
    </section>

    <section class="admin-surface admin-card rounded-2xl p-5 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-violet-300">Aktivitas terbaru</p>
                <h2 class="mt-1 text-lg font-bold text-white">Pergerakan pengguna</h2>
            </div>
            <a href="{{ route('admin.statistics') }}#activity-log" class="text-xs font-bold text-blue-300 transition hover:text-blue-200">Buka log aktivitas →</a>
        </div>
        <div data-live-activities data-empty-message="Belum ada aktivitas yang tercatat." class="mt-5 divide-y divide-slate-800/80">
            @forelse($activities as $activity)
                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $activity->type === 'completed' ? 'bg-emerald-400' : 'bg-violet-400' }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-200"><span class="font-bold text-white">{{ $activity->user?->name ?? 'Pengguna dihapus' }}</span> {{ $activity->action }}</p>
                            @if($activity->subject)
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $activity->subject }}</p>
                            @endif
                        </div>
                    </div>
                    <time class="shrink-0 text-xs text-slate-500">{{ $activity->created_at->format('d M Y, H:i') }}</time>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-slate-500">Belum ada aktivitas yang tercatat.</p>
            @endforelse
        </div>
    </section>
    </div>
@endsection
