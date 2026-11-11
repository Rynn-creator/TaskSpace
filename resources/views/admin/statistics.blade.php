@extends('layouts.admin')

@section('title', 'Statistik & Log')

@section('content')
    <div data-live-page="statistics" data-live-url="{{ route('admin.live.statistics', request()->only('page')) }}" class="space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Analisis aplikasi</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Statistik &amp; Log</h1>
        <p class="mt-2 text-sm text-slate-400">Tren pendaftaran dan aktivitas tugas selama 14 hari terakhir.</p>
      </div>
      <div class="flex items-center gap-3">
          <div class="text-right">
              <p class="flex items-center justify-end gap-2 text-xs font-bold text-emerald-300"><span data-live-dot class="h-2 w-2 rounded-full bg-slate-500"></span><span data-live-status>Memulai...</span></p>
              <p class="mt-1 text-[10px] text-slate-500">Terakhir diperbarui <time data-live-updated>—</time></p>
          </div>
          <button type="button" data-live-refresh class="rounded-xl border border-slate-700 bg-slate-900/70 px-3 py-2.5 text-xs font-bold text-slate-300 transition hover:border-violet-400/40 hover:text-white">Refresh</button>
      </div>
    </header>

    @php
        $maxDaily = max(array_merge($userTrend, $taskTrend, $completedTrend, [1]));
        $priorityTotal = array_sum($priorityCounts->all());
    @endphp

    <section class="admin-surface admin-card rounded-2xl p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-blue-300">Pertumbuhan &amp; aktivitas</p>
                <h2 class="mt-1 text-lg font-bold text-white">Aktivitas harian</h2>
            </div>
            <div class="flex flex-wrap gap-3 text-[11px] font-semibold text-slate-400">
                <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-blue-400"></i>Pengguna baru</span>
                <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-violet-400"></i>Tugas dibuat</span>
                <span class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-emerald-400"></i>Tugas selesai</span>
            </div>
        </div>
        <div data-live-trend class="mt-6 grid grid-cols-7 gap-2 sm:grid-cols-[repeat(14,minmax(0,1fr))]" aria-label="Grafik batang aktivitas 14 hari">
            @foreach($labels as $index => $label)
                <div class="live-trend-day group min-w-0 text-center" data-day="{{ $index }}" data-label="{{ $label }}">
                    <div class="flex h-40 items-end justify-center gap-0.5 border-b border-slate-800 pb-1">
                        @foreach([[$userTrend[$index], 'bg-blue-400', 'users', 'Pengguna baru'], [$taskTrend[$index], 'bg-violet-400', 'tasks', 'Tugas dibuat'], [$completedTrend[$index], 'bg-emerald-400', 'completed', 'Tugas selesai']] as [$count, $color, $series, $seriesLabel])
                            <div data-series="{{ $series }}" class="chart-bar-hitbox w-1.5 rounded-t-sm {{ $color }} transition-[height] duration-700 ease-out group-hover:opacity-75 sm:w-2"
                                style="height: {{ $count > 0 ? max(4, ($count / $maxDaily) * 100) : 1 }}%"
                                data-tooltip-label="{{ $seriesLabel }}"
                                data-tooltip-value="{{ $count }}"
                                role="img"
                                tabindex="0"
                                aria-label="{{ $seriesLabel }}, {{ $label }}: {{ $count }}"></div>
                        @endforeach
                    </div>
                    <p class="mt-2 truncate text-[9px] text-slate-500 sm:text-[10px]">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-3">
        <article class="admin-surface admin-card rounded-2xl p-5" style="animation-delay: 70ms">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-300">Prioritas tugas</p>
            <h2 class="mt-1 text-lg font-bold text-white">Perbandingan prioritas</h2>
            <div class="mt-5 space-y-4">
                @foreach([['High', 'Tinggi', 'bg-rose-400'], ['Medium', 'Sedang', 'bg-amber-400'], ['Low', 'Rendah', 'bg-blue-400']] as [$key, $label, $color])
                    @php($count = (int) ($priorityCounts[$key] ?? 0))
                    <div>
                        <div class="mb-1.5 flex justify-between text-xs"><span class="text-slate-300">{{ $label }}</span><span class="font-bold text-white"><span data-priority-value="{{ $key }}">{{ $count }}</span> <span data-priority-percent="{{ $key }}" class="font-normal text-slate-500">({{ $priorityTotal ? round($count / $priorityTotal * 100) : 0 }}%)</span></span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-800"><div data-priority-bar="{{ $key }}" class="h-full rounded-full {{ $color }} transition-all duration-700" style="width: {{ $priorityTotal ? round($count / $priorityTotal * 100) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="admin-surface admin-card rounded-2xl p-5" style="animation-delay: 120ms">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-300">Status tugas</p>
            <h2 class="mt-1 text-lg font-bold text-white">Tugas berdasarkan status</h2>
            <div class="mt-5 space-y-4">
                @foreach([['Selesai', $statusCounts['completed'], 'bg-emerald-400'], ['Belum selesai', $statusCounts['pending'], 'bg-amber-400']] as [$label, $count, $color])
                    <div>
                        @php($statusKey = $label === 'Selesai' ? 'completed' : 'pending')
                        <div class="mb-1.5 flex justify-between text-xs"><span class="text-slate-300">{{ $label }}</span><span data-status-value="{{ $statusKey }}" class="font-bold text-white">{{ $count }}</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-800"><div data-status-bar="{{ $statusKey }}" class="h-full rounded-full {{ $color }} transition-all duration-700" style="width: {{ $totalTasks ? round($count / $totalTasks * 100) : 0 }}%"></div></div>
                    </div>
                @endforeach
                <div class="flex items-center justify-between border-t border-slate-800 pt-4 text-sm">
                    <span class="text-slate-400">Tugas terlambat</span>
                    <span data-overdue-count class="font-bold text-rose-300">{{ $overdueTasks }}</span>
                </div>
            </div>
        </article>

        <article class="admin-surface admin-card rounded-2xl p-5" style="animation-delay: 170ms">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">Keterangan data</p>
            <h2 class="mt-1 text-lg font-bold text-white">Sumber statistik</h2>
            <p class="mt-4 text-sm leading-6 text-slate-400">Pendaftaran dan tugas dibuat dihitung dari tanggal pembuatan data. Penyelesaian dihitung dari log saat pengguna menyelesaikan tugas, sedangkan status dan keterlambatan berasal dari status tugas saat ini.</p>
            <p class="mt-3 rounded-xl border border-slate-800 bg-slate-950/50 p-3 text-xs leading-5 text-slate-500">Log yang tersedia mencatat aktivitas tugas pengguna. Aplikasi belum mencatat riwayat tindakan admin.</p>
        </article>
    </section>

    <section id="activity-log" class="admin-surface admin-card scroll-mt-6 overflow-hidden rounded-2xl">
        <div class="border-b border-slate-800 px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-wider text-violet-300">Aktivitas pengguna</p>
            <h2 class="mt-1 text-lg font-bold text-white">Log aktivitas terbaru</h2>
        </div>
        <div data-live-activities data-empty-message="Belum ada aktivitas yang tercatat." class="divide-y divide-slate-800/70">
            @forelse($activities as $activity)
                <article class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-200"><span class="font-bold text-white">{{ $activity->user?->name ?? 'Pengguna dihapus' }}</span> {{ $activity->action }}</p>
                        @if($activity->subject)<p class="mt-1 text-xs text-slate-500">{{ $activity->subject }}</p>@endif
                    </div>
                    <time class="shrink-0 text-xs text-slate-500">{{ $activity->created_at->format('d M Y, H:i') }}</time>
                </article>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Belum ada aktivitas yang tercatat.</p>
            @endforelse
        </div>
        @if($activities->hasPages())
            <div data-activity-pagination class="border-t border-slate-800 px-5 py-4">{{ $activities->links() }}</div>
        @endif
    </section>
    </div>
@endsection
