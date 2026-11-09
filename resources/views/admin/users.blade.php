<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna — TaskSpace</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes userCardIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-card { animation: userCardIn 420ms ease-out both; }
        .taskspace-confirm-popup {
            border: 1px solid rgba(51, 65, 85, 0.9) !important;
            border-radius: 1.25rem !important;
            background: rgba(15, 23, 42, 0.96) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65) !important;
            backdrop-filter: blur(20px);
        }
        .taskspace-confirm-title { color: #f8fafc !important; }
        .taskspace-confirm-content { color: #94a3b8 !important; }
    </style>
</head>
<body class="min-h-screen bg-[#070b14] text-slate-100 antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        @include('partials.sidebar')
        <main class="min-w-0 flex-1 p-6 lg:p-10">
            <div class="mx-auto max-w-5xl space-y-6">
                <header class="animate-entry">
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-400">Admin</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-white">Pengguna</h1>
                    <p class="mt-1 text-sm text-slate-400">Kelola role, akses akun, dan status kehadiran. Akun Anda sendiri tidak ditampilkan.</p>
                </header>

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
                @endif

                <section class="grid gap-4 sm:grid-cols-2 animate-entry delay-100">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Pengguna</p>
                        <p class="mt-3 text-3xl font-extrabold text-white">{{ $userCount }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-500/20 bg-slate-900/60 p-5">
                        <p class="text-xs font-bold uppercase tracking-widest text-emerald-400">Sedang Online</p>
                        <p class="mt-3 text-3xl font-extrabold text-emerald-400">{{ $onlineCount }}</p>
                        <p class="mt-1 text-xs text-slate-500">Aktif dalam 5 menit terakhir</p>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 shadow-2xl animate-entry delay-200">
                    <div class="border-b border-slate-800 px-5 py-4">
                        <h2 class="text-sm font-bold text-white">Daftar Akun</h2>
                    </div>
                    <div class="divide-y divide-slate-800/70">
                        @forelse($users as $index => $user)
                            @php
                                $isOnline = $user->last_seen_at !== null
                                    && \Illuminate\Support\Carbon::parse($user->last_seen_at)->greaterThanOrEqualTo($onlineSince);
                            @endphp
                            <article class="user-card flex flex-col gap-4 p-5 transition hover:bg-slate-800/30 xl:flex-row xl:items-center xl:justify-between" style="animation-delay: {{ $index * 60 }}ms">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $user->is_suspended ? 'bg-rose-500/15 text-rose-300' : 'bg-indigo-500/15 text-indigo-300' }} font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-white">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $user->role === 'admin' ? 'border-violet-500/20 bg-violet-500/10 text-violet-300' : 'border-slate-700 bg-slate-800/70 text-slate-300' }}">{{ $user->role === 'admin' ? 'Admin' : 'User' }}</span>
                                            @if($user->is_suspended)
                                                <span class="rounded-full border border-rose-500/20 bg-rose-500/10 px-2.5 py-1 text-[10px] font-bold text-rose-300">Suspend</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 xl:items-end">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full {{ $isOnline && ! $user->is_suspended ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                                        <span class="text-xs font-semibold {{ $isOnline && ! $user->is_suspended ? 'text-emerald-300' : 'text-slate-400' }}">{{ $isOnline && ! $user->is_suspended ? 'Online' : 'Offline' }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex items-center gap-2" data-confirm="role" data-user-name="{{ $user->name }}" data-current-role="{{ $user->role }}">
                                            @csrf
                                            @method('PATCH')
                                            <label for="user-role-{{ $user->id }}" class="sr-only">Role untuk {{ $user->name }}</label>
                                            <select id="user-role-{{ $user->id }}" name="role" class="rounded-lg border border-slate-700 bg-slate-950/80 px-2.5 py-2 text-xs font-semibold text-slate-200 focus:border-indigo-500 focus:outline-none">
                                                <option value="user" @selected($user->role === 'user')>User</option>
                                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                            </select>
                                            <button type="submit" class="rounded-lg border border-indigo-500/30 bg-indigo-500/10 px-3 py-2 text-xs font-bold text-indigo-300 transition hover:bg-indigo-500/20">Ubah Role</button>
                                        </form>

                                        <form action="{{ route('admin.users.suspension', $user) }}" method="POST" data-confirm="{{ $user->is_suspended ? 'activate' : 'suspend' }}" data-user-name="{{ $user->name }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border px-3 py-2 text-xs font-bold transition {{ $user->is_suspended ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20' : 'border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' }}">
                                                {{ $user->is_suspended ? 'Aktifkan' : 'Suspend' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" data-confirm="delete" data-user-name="{{ $user->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500/20">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="p-8 text-center text-sm text-slate-400">Belum ada akun pengguna.</p>
                        @endforelse
                    </div>
                    @if($users->hasPages())
                        <div class="border-t border-slate-800 px-5 py-4">{{ $users->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
    <script>
        const userActionConfirm = Swal.mixin({
            background: 'rgba(15, 23, 42, 0.96)',
            color: '#f8fafc',
            buttonsStyling: false,
            customClass: {
                popup: 'taskspace-confirm-popup',
                title: 'taskspace-confirm-title',
                htmlContainer: 'taskspace-confirm-content',
                confirmButton: 'rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-500',
                cancelButton: 'rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-bold text-slate-300 transition hover:bg-slate-700',
                actions: 'gap-3'
            }
        });

        const confirmationCopy = {
            role: {
                title: 'Ubah role pengguna?',
                confirm: 'Ya, ubah role',
                icon: 'question',
                confirmClass: 'bg-indigo-600 hover:bg-indigo-500'
            },
            suspend: {
                title: 'Suspend akun ini?',
                text: 'Pengguna tidak akan dapat login sampai akun diaktifkan kembali.',
                confirm: 'Ya, suspend',
                icon: 'warning',
                confirmClass: 'bg-amber-600 hover:bg-amber-500'
            },
            activate: {
                title: 'Aktifkan kembali akun?',
                text: 'Pengguna akan dapat login kembali.',
                confirm: 'Ya, aktifkan',
                icon: 'question',
                confirmClass: 'bg-emerald-600 hover:bg-emerald-500'
            },
            delete: {
                title: 'Hapus akun secara permanen?',
                text: 'Task dan data terkait akun ini juga dapat ikut terhapus. Tindakan ini tidak dapat dibatalkan.',
                confirm: 'Ya, hapus akun',
                icon: 'error',
                confirmClass: 'bg-rose-600 hover:bg-rose-500'
            }
        };

        document.querySelectorAll('form[data-confirm]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (form.dataset.confirmed === 'true') {
                    return;
                }

                event.preventDefault();

                const action = form.dataset.confirm;
                const copy = confirmationCopy[action];
                const userName = form.dataset.userName;
                let confirmationText = copy.text ?? '';

                if (action === 'role') {
                    const selectedRole = form.querySelector('select[name="role"]').value;
                    if (selectedRole === form.dataset.currentRole) {
                        await userActionConfirm.fire({
                            title: 'Role belum berubah',
                            text: `Pilih role yang berbeda untuk ${userName}.`,
                            icon: 'info',
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }

                    confirmationText = `Role ${userName} akan diubah menjadi ${selectedRole === 'admin' ? 'Admin' : 'User'}.`;
                } else {
                    confirmationText = `${userName}. ${confirmationText}`;
                }

                const result = await userActionConfirm.fire({
                    title: copy.title,
                    text: confirmationText,
                    icon: copy.icon,
                    showCancelButton: true,
                    confirmButtonText: copy.confirm,
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: {
                        popup: 'taskspace-confirm-popup',
                        title: 'taskspace-confirm-title',
                        htmlContainer: 'taskspace-confirm-content',
                        confirmButton: `rounded-xl px-5 py-2.5 text-sm font-bold text-white transition ${copy.confirmClass}`,
                        cancelButton: 'rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-sm font-bold text-slate-300 transition hover:bg-slate-700',
                        actions: 'gap-3'
                    }
                });

                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.requestSubmit();
                }
            });
        });
    </script>
</body>
</html>
