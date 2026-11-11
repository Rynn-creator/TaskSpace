@extends('layouts.admin')

@section('title', 'Pengaturan Profil')

@section('content')
    <header>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Akun TaskSpace</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Pengaturan Profil</h1>
        <p class="mt-2 text-sm text-slate-400">Kelola nama dan foto profil akun Anda.</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div role="status" class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-300">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="admin-surface admin-card max-w-3xl rounded-2xl p-5 sm:p-8">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-7">
            @csrf
            @method('PATCH')

            <div class="flex flex-col gap-5 border-b border-slate-800 pb-7 sm:flex-row sm:items-center">
                <div class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-full border border-slate-700 bg-slate-800 shadow-lg transition duration-300 hover:scale-[1.03] hover:border-violet-400/50">
                    @if($user->profile_photo_path)
                        <img data-profile-preview src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover transition-opacity duration-300">
                        <span data-profile-initial class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-violet-500/20 to-blue-500/20 text-3xl font-extrabold text-violet-200">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @else
                        <img data-profile-preview src="" alt="Pratinjau foto profil" class="hidden h-full w-full object-cover transition-opacity duration-300">
                        <span data-profile-initial class="flex h-full w-full items-center justify-center bg-gradient-to-br from-violet-500/20 to-blue-500/20 text-3xl font-extrabold text-violet-200">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="space-y-2">
                    <h2 class="font-bold text-white">Foto profil</h2>
                    <p class="text-xs leading-5 text-slate-400">JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 2 MB.</p>
                    <input id="profile-photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-profile-photo-button class="rounded-xl border border-violet-500/30 bg-violet-500/10 px-4 py-2 text-xs font-bold text-violet-200 transition hover:bg-violet-500/20">
                            {{ $user->profile_photo_path ? 'Ganti Foto' : 'Upload Foto' }}
                        </button>
                        @if($user->profile_photo_path)
                            <button type="submit" form="delete-profile-photo" class="rounded-xl border border-rose-500/25 bg-rose-500/10 px-4 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500/20" onclick="return confirm('Hapus foto profil dan gunakan avatar inisial?')">Hapus Foto</button>
                        @endif
                    </div>
                    <p data-profile-file-name class="text-xs text-slate-500" aria-live="polite"></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="profile-name" class="mb-2 block text-sm font-semibold text-slate-300">Nama</label>
                    <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" required class="w-full rounded-xl border border-slate-700 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition focus:border-violet-400">
                </div>
                <div>
                    <label for="profile-email" class="mb-2 block text-sm font-semibold text-slate-300">Email</label>
                    <input id="profile-email" type="email" value="{{ $user->email }}" readonly class="w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-900/60 px-4 py-3 text-sm text-slate-500">
                    <p class="mt-1.5 text-xs text-slate-500">Email tidak dapat diubah dari pengaturan profil.</p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-800 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ $user->isAdmin() ? route('admin.dashboard') : route('tasks.index') }}" class="rounded-xl border border-slate-700 px-5 py-3 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-800">Batal</a>
                <button type="submit" class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-violet-500">Simpan Perubahan</button>
            </div>
        </form>

        @if($user->profile_photo_path)
            <form id="delete-profile-photo" action="{{ route('profile.photo.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </section>

    <script>
        (() => {
            const input = document.getElementById('profile-photo');
            const button = document.querySelector('[data-profile-photo-button]');
            const preview = document.querySelector('[data-profile-preview]');
            const initial = document.querySelector('[data-profile-initial]');
            const fileName = document.querySelector('[data-profile-file-name]');
            if (!input || !button || !preview || !initial || !fileName) return;

            let previewUrl = null;
            button.addEventListener('click', () => input.click());
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
                preview.classList.remove('hidden');
                initial.classList.add('hidden');
                initial.classList.remove('flex');
                fileName.textContent = file.name;
                button.textContent = 'Ganti Foto';
            });
        })();
    </script>
@endsection
