@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')
    <header>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-violet-300">Administrasi akun</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Kelola Pengguna</h1>
        <p class="mt-2 text-sm text-slate-400">Cari akun, periksa status dan kehadiran, lalu buka detail untuk tindakan admin.</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2">
        <article class="admin-surface admin-card rounded-2xl p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total pengguna</p>
            <p class="mt-3 text-3xl font-extrabold text-white">{{ number_format($userCount) }}</p>
            <p class="mt-1 text-xs text-slate-500">Akun dengan role pengguna</p>
        </article>
        <article class="admin-surface admin-card rounded-2xl p-5" style="animation-delay: 60ms">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">Sedang online</p>
            <p class="mt-3 text-3xl font-extrabold text-emerald-300">{{ number_format($onlineCount) }}</p>
            <p class="mt-1 text-xs text-slate-500">Aktif dalam 5 menit terakhir</p>
        </article>
    </section>

    <section class="admin-surface overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-4 border-b border-slate-800 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-white">Daftar akun</h2>
                <p class="mt-1 text-xs text-slate-500">Akun admin yang sedang login tidak ditampilkan.</p>
            </div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
                <label for="user-search" class="sr-only">Cari nama atau email</label>
                <input id="user-search" name="search" value="{{ request('search') }}" type="search" placeholder="Cari nama atau email"
                    class="min-w-0 rounded-xl border border-slate-700 bg-slate-950/70 px-3 py-2.5 text-sm text-white placeholder-slate-500 outline-none transition focus:border-violet-400 sm:w-72">
                <button class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-500">Cari</button>
                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-700 px-3 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">Reset</a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[780px] text-left text-sm">
                <thead class="bg-slate-950/50 text-[10px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-bold">Pengguna</th>
                        <th class="px-4 py-3 font-bold">Role</th>
                        <th class="px-4 py-3 font-bold">Status</th>
                        <th class="px-4 py-3 font-bold">Bergabung</th>
                        <th class="px-4 py-3 font-bold">Terakhir online</th>
                        <th class="px-5 py-3 text-right font-bold">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/70">
                    @forelse($users as $user)
                        @php
                            $isOnline = $user->last_seen_at !== null
                                && \Illuminate\Support\Carbon::parse($user->last_seen_at)->greaterThanOrEqualTo($onlineSince)
                                && ! $user->is_suspended;
                        @endphp
                        <tr class="transition hover:bg-slate-800/25">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @include('partials.profile-avatar', [
                                        'profileUser' => $user,
                                        'avatarClass' => 'flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-500/10 font-bold text-violet-200',
                                    ])
                                    <span class="min-w-0"><span class="block truncate font-semibold text-white">{{ $user->name }}</span><span class="block truncate text-xs text-slate-500">{{ $user->email }}</span></span>
                                </div>
                            </td>
                            <td class="px-4 py-4"><span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $user->role === 'admin' ? 'border-violet-500/20 bg-violet-500/10 text-violet-300' : 'border-slate-700 bg-slate-800/70 text-slate-300' }}">{{ $user->role === 'admin' ? 'Admin' : 'Pengguna' }}</span></td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-2 text-xs {{ $user->is_suspended ? 'text-rose-300' : 'text-emerald-300' }}">
                                    <i class="h-2 w-2 rounded-full {{ $user->is_suspended ? 'bg-rose-400' : 'bg-emerald-400' }}"></i>{{ $user->is_suspended ? 'Nonaktif' : 'Aktif' }}
                                </span>
                                <span class="mt-1 block text-[10px] {{ $isOnline ? 'text-cyan-300' : 'text-slate-500' }}">{{ $isOnline ? 'Online' : 'Offline' }}</span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-400">{{ $user->last_seen_at ? \Illuminate\Support\Carbon::parse($user->last_seen_at)->format('d M Y, H:i') : 'Belum tercatat' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex rounded-lg border border-violet-500/25 bg-violet-500/10 px-3 py-2 text-xs font-bold text-violet-200 transition hover:bg-violet-500/20">Detail</a>
                                    <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="flex gap-1" onsubmit="return confirm('Ubah role pengguna ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <label for="role-{{ $user->id }}" class="sr-only">Role pengguna</label>
                                        <select id="role-{{ $user->id }}" name="role" class="max-w-24 rounded-lg border border-slate-700 bg-slate-950 px-2 text-[10px] text-slate-300">
                                            <option value="user" @selected($user->role === 'user')>Pengguna</option>
                                            <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                        </select>
                                        <button class="rounded-lg border border-blue-500/20 px-2 py-2 text-[10px] font-bold text-blue-200 transition hover:bg-blue-500/10">Ubah Role</button>
                                    </form>
                                    <form action="{{ route('admin.users.suspension', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_suspended ? 'Aktifkan' : 'Suspend' }} akun ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-lg border border-amber-500/20 px-2 py-2 text-[10px] font-bold text-amber-200 transition hover:bg-amber-500/10">{{ $user->is_suspended ? 'Aktifkan' : 'Suspend' }}</button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini secara permanen? Data terkait juga dapat ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-500/20 px-2 py-2 text-[10px] font-bold text-rose-200 transition hover:bg-rose-500/10">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ request('search') ? 'Tidak ada pengguna yang cocok dengan pencarian.' : 'Belum ada akun pengguna.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="border-t border-slate-800 px-5 py-4">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
