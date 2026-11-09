<aside class="flex w-full shrink-0 flex-col justify-between border-b border-slate-800/60 bg-slate-900/40 p-5 backdrop-blur-2xl md:min-h-screen md:w-64 md:border-b-0 md:border-r md:p-6">
    <div>
        <a href="{{ route('tasks.index') }}" class="mb-8 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white shadow-lg shadow-violet-600/30">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-extrabold tracking-tight text-white">TaskSpace</p>
                <span class="rounded-full border border-slate-700/60 bg-slate-800/80 px-2 py-0.5 text-[10px] font-bold text-slate-400">Search Edition</span>
            </div>
        </a>

        <nav class="space-y-2">
            @if(Auth::user()->isAdmin())
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Admin</p>
                <a href="{{ route('tasks.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('tasks.index') && request()->query('section') !== 'analytics' ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Dashboard</a>
                <a href="{{ route('tasks.index', ['section' => 'analytics']) }}#analytics" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->query('section') === 'analytics' ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M18 17V9m-5 8V5m-5 12v-5"/></svg>
                    <span>Statistik &amp; Log</span>
                </a>
                <a href="{{ route('admin.feedback.index') }}" class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.feedback.*') ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span>Feedback Masuk</span>
                    @if($unansweredFeedbackCount > 0)
                        <span class="rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-extrabold text-rose-300">{{ $unansweredFeedbackCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.password-requests.index') }}" class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.password-requests.*') ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <span>Request Password</span>
                    @if($pendingPasswordResetCount > 0)
                        <span class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-extrabold text-amber-300">{{ $pendingPasswordResetCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.users.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Pengguna</a>
            @else
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Dashboard</p>
                <a href="{{ route('tasks.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-slate-800/70 hover:text-white">Semua Task</a>
                <a href="{{ route('tasks.index', ['filter' => 'pending']) }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Pending</a>
                <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" class="block rounded-xl px-4 py-3 text-sm font-semibold text-slate-400 transition hover:bg-slate-800/70 hover:text-white">Selesai</a>
                <p class="mb-2 px-3 pt-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">Bantuan</p>
                <a href="{{ route('feedback.index') }}" class="block rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('feedback.*') ? 'border border-indigo-500/30 bg-indigo-500/10 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Kirim Feedback</a>
            @endif
        </nav>
    </div>

    <div class="mt-6 flex items-center justify-between border-t border-slate-800/60 pt-4">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-700/80 bg-slate-800 text-sm font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div class="min-w-0">
                <p class="truncate text-xs font-bold text-white">{{ Auth::user()->name }}</p>
                <p class="truncate text-[10px] text-slate-500">{{ Auth::user()->isAdmin() ? 'Admin' : Auth::user()->email }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-xl p-2 text-slate-400 transition hover:bg-rose-500/10 hover:text-rose-400" title="Logout">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-16V7"/></svg>
            </button>
        </form>
    </div>
</aside>
<script>
    const heartbeatUrl = @json(route('session.heartbeat'));
    const sendHeartbeat = () => fetch(heartbeatUrl, { credentials: 'same-origin' });
    sendHeartbeat();
    window.setInterval(sendHeartbeat, 60000);
</script>
