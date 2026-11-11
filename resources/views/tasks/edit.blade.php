@extends('layouts.admin')

@section('title', 'Edit ' . $task->title)

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <a href="{{ route('tasks.show', $task) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 transition hover:text-white"><span aria-hidden="true">←</span> Kembali ke Detail Tugas</a>

        @if($errors->any())
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
                <p class="font-bold">Periksa kembali data tugas dan lampiran.</p>
                <ul class="mt-2 list-inside list-disc text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="admin-surface admin-card rounded-3xl p-6 sm:p-8">
            <h1 class="text-xl font-extrabold text-white">Edit Tugas</h1>
            <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @method('PATCH')
                <div>
                    <label for="title" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Nama tugas</label>
                    <input id="title" name="title" value="{{ old('title', $task->title) }}" required maxlength="255" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label for="description" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi</label>
                    <textarea id="description" name="description" rows="5" maxlength="10000" class="w-full resize-y rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="category" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Kategori / proyek</label>
                        <input id="category" name="category" value="{{ old('category', $task->category) }}" required maxlength="255" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="priority" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Prioritas</label>
                        <select id="priority" name="priority" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                            @foreach(['Low', 'Medium', 'High'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', $task->priority) === $priority)>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="due_date" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Deadline</label>
                    <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label for="attachments" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Tambah lampiran (maks. 10 MB per file)</label>
                    <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.txt,.csv,.zip" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-xs text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-500/15 file:px-3 file:py-2 file:font-bold file:text-indigo-300">
                </div>
                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-800 pt-5">
                    <a href="{{ route('tasks.show', $task) }}" class="rounded-xl px-4 py-3 text-xs font-bold text-slate-400 transition hover:bg-slate-800">Batal</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-xs font-extrabold text-white transition hover:bg-indigo-500">Simpan Perubahan</button>
                </div>
            </form>
        </section>
    </div>
@endsection
