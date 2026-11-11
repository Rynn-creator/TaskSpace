@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-blue-300 transition hover:text-blue-200">← Kembali ke pengguna</a>
            <p class="mt-4 text-xs font-bold uppercase tracking-[.18em] text-violet-300">Profil pengguna</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Detail Pengguna</h1>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $isOnline ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-slate-700 bg-slate-800/60 text-slate-400' }}">
            <i class="h-2 w-2 rounded-full {{ $isOnline ? 'bg-emerald-400' : 'bg-slate-500' }}"></i>{{ $isOnline ? 'Online' : 'Offline' }}
        </span>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        <section class="admin-surface admin-card rounded-2xl p-5 sm:p-6">
            <div class="flex items-center gap-4 border-b border-slate-800 pb-5">
                @include('partials.profile-avatar', [
                    'profileUser' => $user,
                    'avatarClass' => 'flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500/20 to-blue-500/20 text-xl font-extrabold text-violet-200',
                ])
                <div class="min-w-0"><h2 class="truncate text-lg font-bold text-white">{{ $user->name }}</h2><p class="truncate text-sm text-slate-400">{{ $user->email }}</p></div>
            </div>
            <h3 class="mt-5 text-xs font-bold uppercase tracking-wider text-violet-300">Informasi akun</h3>
            <dl class="mt-3 divide-y divide-slate-800/80">
                <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Role</dt><dd class="font-semibold text-slate-200">{{ $user->role === 'admin' ? 'Admin' : 'Pengguna' }}</dd></div>
                <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Status akun</dt><dd class="font-semibold {{ $user->is_suspended ? 'text-rose-300' : 'text-emerald-300' }}">{{ $user->is_suspended ? 'Nonaktif' : 'Aktif' }}</dd></div>
                <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Tanggal bergabung</dt><dd class="text-right text-slate-200">{{ $user->created_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4 py-3 text-sm"><dt class="text-slate-500">Terakhir online</dt><dd class="text-right text-slate-200">{{ $lastSeenAt ? \Illuminate\Support\Carbon::parse($lastSeenAt)->format('d M Y, H:i') : 'Belum tercatat' }}</dd></div>
            </dl>

            @if(! request()->user()->is($user))
                <div class="mt-5 space-y-4 border-t border-slate-800 pt-5">
                    <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex flex-wrap items-center gap-2" onsubmit="return confirm('Ubah role pengguna ini?')">
                        @csrf
                        @method('PATCH')
                        <label for="user-role" class="mr-auto text-sm font-semibold text-slate-300">Ubah role</label>
                        <select id="user-role" name="role" class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-200">
                            <option value="user" @selected($user->role === 'user')>Pengguna</option>
                            <option value="admin" @selected($user->role === 'admin')>Admin</option>
                        </select>
                        <button class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-500">Simpan role</button>
                    </form>
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.users.suspension', $user) }}" method="POST" onsubmit="return confirm('{{ $user->is_suspended ? 'Aktifkan kembali akun ini?' : 'Nonaktifkan akun ini?' }}')">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-lg border px-3 py-2 text-xs font-bold transition {{ $user->is_suspended ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20' : 'border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' }}">{{ $user->is_suspended ? 'Aktifkan akun' : 'Nonaktifkan akun' }}</button>
                        </form>
                        @if($user->role === 'user')
                            <form action="{{ route('admin.users.password-reset', $user) }}" method="POST" onsubmit="return confirm('Kirim tautan reset untuk pengguna ini?')">
                                @csrf
                                <button class="rounded-lg border border-blue-500/30 bg-blue-500/10 px-3 py-2 text-xs font-bold text-blue-200 transition hover:bg-blue-500/20">Kirim tautan reset</button>
                            </form>
                        @endif
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini secara permanen? Data terkait juga dapat ikut terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-bold text-rose-300 transition hover:bg-rose-500/20">Hapus akun</button>
                        </form>
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl border border-slate-800 bg-slate-950/50 p-3 text-xs leading-5 text-slate-400">Tindakan role dan status akun dinonaktifkan untuk melindungi akun admin yang sedang login.</p>
            @endif
        </section>

        <section class="space-y-5">
            <article class="admin-surface admin-card rounded-2xl p-5 sm:p-6" style="animation-delay: 70ms">
                <h2 class="text-xs font-bold uppercase tracking-wider text-blue-300">Aktivitas pengguna</h2>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @foreach([['Total tugas', $totalTasks, 'text-white'], ['Tugas selesai', $completedTasks, 'text-emerald-300'], ['Belum selesai', $pendingTasks, 'text-amber-300'], ['Terlambat', $overdueTasks, 'text-rose-300']] as [$label, $count, $color])
                        <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4"><p class="text-xs text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-extrabold {{ $color }}">{{ number_format($count) }}</p></div>
                    @endforeach
                </div>
            </article>

            @php($priorityTotal = array_sum($priorityCounts->all()))
            <article class="admin-surface admin-card rounded-2xl p-5 sm:p-6" style="animation-delay: 130ms">
                <h2 class="text-xs font-bold uppercase tracking-wider text-violet-300">Distribusi prioritas</h2>
                <div class="mt-5 space-y-4">
                    @foreach([['High', 'Tinggi', 'bg-rose-400'], ['Medium', 'Sedang', 'bg-amber-400'], ['Low', 'Rendah', 'bg-blue-400']] as [$key, $label, $color])
                        @php($count = (int) ($priorityCounts[$key] ?? 0))
                        <div>
                            <div class="mb-1.5 flex justify-between text-xs"><span class="text-slate-300">{{ $label }}</span><span class="font-bold text-white">{{ $count }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800"><div class="h-full rounded-full {{ $color }} transition-all duration-700" style="width: {{ $priorityTotal ? round($count / $priorityTotal * 100) : 0 }}%"></div></div>
                        </div>
                    @endforeach
                    @if($priorityTotal === 0)<p class="text-xs text-slate-500">Belum ada tugas untuk ditampilkan.</p>@endif
                </div>
            </article>
        </section>
    </div>
@endsection
