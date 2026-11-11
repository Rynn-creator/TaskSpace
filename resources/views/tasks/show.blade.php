@extends('layouts.admin')

@section('title', $task->title)

@section('content')
    @php
        $isOverdue = ! $task->is_completed && $task->due_date && $task->due_date->lt(today());
        $isDueToday = ! $task->is_completed && $task->due_date && $task->due_date->isToday();
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 transition hover:text-white">
            <span aria-hidden="true">←</span> Kembali ke Daftar Tugas
        </a>

        @if($isOverdue || $isDueToday)
            <section class="rounded-2xl border {{ $isOverdue ? 'border-rose-500/30 bg-rose-500/10' : 'border-amber-500/30 bg-amber-500/10' }} p-5" role="status">
                <h2 class="text-sm font-extrabold {{ $isOverdue ? 'text-rose-300' : 'text-amber-300' }}">{{ $isOverdue ? 'Deadline Terlewat!' : 'Pengingat Deadline!' }}</h2>
                <p class="mt-1 text-sm text-slate-300">
                    {{ $isOverdue ? 'Tugas' : 'Tugas' }} <span class="font-bold text-white">{{ $task->title }}</span>
                    {{ $isOverdue ? 'belum selesai dan sudah melewati batas waktu.' : 'memiliki deadline hari ini. Jangan lupa selesaikan pekerjaanmu.' }}
                </p>
            </section>
        @endif

        <section class="admin-surface admin-card overflow-hidden rounded-3xl">
            <div class="border-b border-slate-800/80 bg-gradient-to-r from-indigo-500/10 via-violet-500/5 to-transparent p-6 sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-lg border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 text-xs font-bold text-indigo-300">#{{ $task->category }}</span>
                            <span class="rounded-lg border px-2.5 py-1 text-xs font-bold {{ $task->is_completed ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-amber-500/20 bg-amber-500/10 text-amber-300' }}">{{ $task->is_completed ? 'Selesai' : 'Belum selesai' }}</span>
                            @if($isOverdue)
                                <span class="rounded-lg border border-rose-500/20 bg-rose-500/10 px-2.5 py-1 text-xs font-extrabold text-rose-300">OVERDUE</span>
                            @endif
                        </div>
                        <h1 class="break-words text-2xl font-extrabold tracking-tight text-white sm:text-3xl">{{ $task->title }}</h1>
                    </div>
                    <span class="shrink-0 rounded-xl border border-slate-700 bg-slate-950/60 px-3 py-2 text-xs font-extrabold uppercase tracking-wider {{ $task->priority === 'High' ? 'text-rose-300' : ($task->priority === 'Medium' ? 'text-amber-300' : 'text-slate-300') }}">{{ $task->priority }} priority</span>
                </div>
            </div>

            <div class="space-y-7 p-6 sm:p-8">
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Deskripsi</h2>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-7 text-slate-300">{{ $task->description ?: 'Belum ada deskripsi untuk tugas ini.' }}</p>
                </section>

                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4">
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Deadline</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-200">{{ $task->due_date?->format('d F Y') ?? 'Tidak ditentukan' }}</dd>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4">
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Dibuat</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-200">{{ $task->created_at->format('d F Y, H:i') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/50 p-4 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Terakhir diperbarui</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-200">{{ $task->updated_at->format('d F Y, H:i') }}</dd>
                    </div>
                </dl>

                <section>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Lampiran</h2>
                            <p class="mt-1 text-xs text-slate-500">{{ $task->attachments->count() }} file</p>
                        </div>
                        <a href="{{ route('tasks.edit', $task) }}" class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2.5 text-xs font-bold text-indigo-300 transition hover:bg-indigo-500/20">Tambah Lampiran / Edit</a>
                    </div>
                    <div class="space-y-2">
                        @forelse($task->attachments as $attachment)
                            <div class="flex flex-col gap-3 rounded-xl border border-slate-800 bg-slate-950/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="break-all text-sm font-semibold text-slate-200">{{ $attachment->original_name }}</p>
                                    <p class="mt-1 text-[10px] text-slate-500">{{ number_format($attachment->size / 1024, 1) }} KB</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <a href="{{ route('tasks.attachments.download', $attachment) }}" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-slate-200 transition hover:bg-slate-700">Download</a>
                                    <form action="{{ route('tasks.attachments.destroy', $attachment) }}" method="POST" onsubmit="return confirm('Hapus lampiran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-500/20 px-3 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500/10">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-700 p-5 text-center text-xs text-slate-500">Belum ada lampiran.</p>
                        @endforelse
                    </div>
                </section>

                <div class="flex flex-col gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:flex-wrap sm:items-center">
                    <a href="{{ route('tasks.edit', $task) }}" class="rounded-xl bg-indigo-600 px-4 py-3 text-center text-xs font-extrabold text-white transition hover:bg-indigo-500">Edit Tugas</a>
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="toggle_status" value="1">
                        <button type="submit" class="w-full rounded-xl border border-slate-700 px-4 py-3 text-xs font-bold text-slate-200 transition hover:bg-slate-800">{{ $task->is_completed ? 'Tandai Belum Selesai' : 'Tandai Selesai' }}</button>
                    </form>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Hapus tugas ini beserta lampirannya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-xl border border-rose-500/30 px-4 py-3 text-xs font-bold text-rose-300 transition hover:bg-rose-500/10">Hapus Tugas</button>
                    </form>
                    <a href="{{ route('tasks.index') }}" class="rounded-xl px-4 py-3 text-center text-xs font-bold text-slate-400 transition hover:bg-slate-800 hover:text-white">Kembali ke Daftar Tugas</a>
                </div>
            </div>
        </section>
    </div>
@endsection
