<aside class="flex w-full shrink-0 flex-col justify-between border-b border-slate-800/70 bg-[#0b1120]/95 p-5 backdrop-blur-xl md:sticky md:top-0 md:h-screen md:w-64 md:border-b-0 md:border-r md:p-6">
    <div>
        <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('tasks.index') }}" class="mb-8 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-blue-600 text-white shadow-lg shadow-violet-600/25">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-extrabold tracking-tight text-white">TaskSpace</p>
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Workspace</span>
            </div>
        </a>

        <nav class="space-y-1">
            @if(Auth::user()->isAdmin())
                <p class="mb-2 px-3 pt-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Admin Panel</p>
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">◈</span> Dashboard Admin
                </a>
                <a href="{{ route('tasks.index', ['section' => 'tasks']) }}" class="admin-nav-link mb-4 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->query('section') === 'tasks' ? 'border border-blue-500/20 bg-blue-500/10 text-blue-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">✓</span> Tugas Saya
                </a>
                <a href="{{ route('admin.statistics') }}" class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.statistics') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">▥</span> Statistik &amp; Log
                </a>
                <a href="{{ route('admin.users.index') }}" class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">♙</span> Kelola Pengguna
                </a>
                <a href="{{ route('admin.feedback.index') }}" class="admin-nav-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.feedback.*') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="flex items-center gap-3"><span class="w-5 text-center" aria-hidden="true">✉</span> Feedback Masuk</span>
                    @if(($unansweredFeedbackCount ?? 0) > 0)
                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-extrabold text-rose-300">{{ $unansweredFeedbackCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.password-requests.index') }}" class="admin-nav-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('admin.password-requests.*') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="flex items-center gap-3"><span class="w-5 text-center" aria-hidden="true">↻</span> Request Password</span>
                    @if(($pendingPasswordResetCount ?? 0) > 0)
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-extrabold text-amber-300">{{ $pendingPasswordResetCount }}</span>
                    @endif
                </a>
            @else
                <a href="{{ route('tasks.index') }}" @if(request()->query('filter') === null) aria-current="page" @endif class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->query('filter') === null ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">✓</span> Semua Tugas
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'pending']) }}" @if(request()->query('filter') === 'pending') aria-current="page" @endif class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->query('filter') === 'pending' ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">◷</span> Belum Selesai
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" @if(request()->query('filter') === 'completed') aria-current="page" @endif class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->query('filter') === 'completed' ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">✓</span> Selesai
                </a>
                <p class="mb-2 px-3 pt-5 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Bantuan</p>
                <a href="{{ route('feedback.index') }}" class="admin-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('feedback.*') ? 'border border-violet-500/30 bg-violet-500/10 text-violet-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span class="w-5 text-center" aria-hidden="true">✉</span> Kirim Feedback
                </a>
            @endif
        </nav>
    </div>

    <div class="mt-6 flex items-center justify-between border-t border-slate-800/70 pt-4">
        <a href="{{ route('profile.edit') }}" class="flex min-w-0 items-center gap-3 transition hover:opacity-80" aria-label="Pengaturan profil">
            @include('partials.profile-avatar', [
                'profileUser' => Auth::user(),
                'avatarClass' => 'flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-700 bg-slate-800 text-sm font-bold text-white',
            ])
            <div class="min-w-0">
                <p class="truncate text-xs font-bold text-white">{{ Auth::user()->name }}</p>
                <p class="truncate text-[10px] text-slate-500">{{ Auth::user()->isAdmin() ? 'Administrator' : Auth::user()->email }}</p>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-xl p-2 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-300" title="Keluar" aria-label="Keluar">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-16V7"/></svg>
            </button>
        </form>
    </div>
</aside>
<script>
    const heartbeatUrl = @json(route('session.heartbeat'));
    const sendHeartbeat = () => {
        if (document.visibilityState === 'visible') {
            fetch(heartbeatUrl, { credentials: 'same-origin' });
        }
    };
    sendHeartbeat();
    window.setInterval(sendHeartbeat, 60000);
    document.addEventListener('visibilitychange', sendHeartbeat);
</script>
