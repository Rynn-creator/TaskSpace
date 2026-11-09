<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Space — Ultra Animated Dashboard</title>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Flatpickr CSS & Dark Theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        @keyframes orbFloat1 {
            0%, 100% { transform: translate(0px, 0px) scale(1); opacity: 0.35; }
            50% { transform: translate(40px, -30px) scale(1.2); opacity: 0.65; }
        }
        @keyframes orbFloat2 {
            0%, 100% { transform: translate(0px, 0px) scale(1); opacity: 0.25; }
            50% { transform: translate(-40px, 30px) scale(1.15); opacity: 0.55; }
        }
        @keyframes slideUpEntrance {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        .animate-orb-1 { animation: orbFloat1 8s infinite ease-in-out; }
        .animate-orb-2 { animation: orbFloat2 10s infinite ease-in-out 2s; }
        .animate-entry { animation: slideUpEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .delay-100 { animation-delay: 100ms; opacity: 0; }
        .delay-200 { animation-delay: 200ms; opacity: 0; }
        .delay-300 { animation-delay: 300ms; opacity: 0; }
        .delay-400 { animation-delay: 400ms; opacity: 0; }
    </style>
</head>
<body x-data="{ 
        theme: localStorage.getItem('themeColor') || 'indigo',
        searchQuery: '{{ request('search', '') }}',
        editModalOpen: false,
        editTaskData: { id: '', title: '', category: 'General', priority: 'Medium', due_date: '' },
        setTheme(color) {
            this.theme = color;
            localStorage.setItem('themeColor', color);
        },
        openEditModal(task) {
            this.editTaskData = { ...task };
            this.editModalOpen = true;
            this.$nextTick(() => {
                flatpickr('#edit_due_date_picker', {
                    locale: 'id', dateFormat: 'Y-m-d', altInput: true, altFormat: 'j F Y', defaultDate: task.due_date
                });
            });
        },
        triggerConfetti() { 
            confetti({ particleCount: 70, spread: 80, origin: { y: 0.7 } }); 
        } 
    }"

    class="bg-[#070b14] text-slate-100 min-h-screen flex flex-col md:flex-row antialiased selection:bg-indigo-500 selection:text-white overflow-x-hidden relative">

    <!-- Ambient Glowing Dynamic Background Orbs -->
    <div class="fixed -top-28 -left-28 w-[32rem] h-[32rem] rounded-full blur-[110px] pointer-events-none animate-orb-1 transition-colors duration-500"
         :class="{
             'bg-indigo-600/30': theme === 'indigo',
             'bg-emerald-600/30': theme === 'emerald',
             'bg-rose-600/30': theme === 'rose',
             'bg-amber-600/30': theme === 'amber',
             'bg-violet-600/30': theme === 'violet'
         }"></div>

    <div class="fixed -bottom-28 -right-28 w-[32rem] h-[32rem] bg-violet-600/25 rounded-full blur-[110px] pointer-events-none animate-orb-2"></div>

    @include('partials.sidebar')
    @php
        $isAnalyticsPage = Auth::user()->isAdmin() && request()->query('section') === 'analytics';
    @endphp

    <!-- Main Content Area -->
    <main class="flex-1 p-6 lg:p-10 flex flex-col space-y-6 overflow-y-auto z-10">
        
        <!-- Header & Theme Switcher (Dengan Jam Realtime Digital) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-entry delay-100"
             x-data="{ 
                currentTime: '',
                currentDate: '',
                updateClock() {
                    const now = new Date();
                    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    this.currentDate = now.toLocaleDateString('id-ID', optionsDate);
                    this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
             }"
             x-init="updateClock(); setInterval(() => updateClock(), 1000)">
            
            <div>
                <h2 class="text-2xl font-extrabold text-white tracking-tight">{{ $isAnalyticsPage ? 'Statistik & Analitik' : 'Dashboard & Operations' }}</h2>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $isAnalyticsPage ? 'Pantau tren dan riwayat aktivitas pengguna.' : 'Cari, kelola, dan atur target tugas harian kamu' }}
                </p>
                
                <!-- Widget Jam Realtime Digital -->
                <div class="inline-flex items-center gap-2.5 mt-2.5 px-3 py-1.5 rounded-xl bg-slate-900/90 border border-slate-800/80 shadow-inner">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-slate-300 font-mono" x-text="currentTime">--:--:--</span>
                    <span class="text-slate-600 text-xs">•</span>
                    <span class="text-[11px] font-semibold text-slate-400" x-text="currentDate">--</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Search Input Bar -->
                @unless($isAnalyticsPage)
                <div class="relative min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Cari task..." 
                        class="w-full bg-slate-900/80 border border-slate-800 text-slate-100 placeholder-slate-500 rounded-2xl pl-10 pr-9 py-2.5 text-xs focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-inner"
                    >
                    <button 
                        x-show="searchQuery.length > 0" 
                        @click="searchQuery = ''" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endunless

                <!-- Theme Switcher Buttons -->
                <div class="bg-slate-900/80 border border-slate-800/80 p-2 rounded-2xl flex items-center justify-center gap-2.5 shadow-xl backdrop-blur-xl">
                    <span class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Tema:</span>
                    <button @click="setTheme('indigo')" class="w-5 h-5 rounded-full bg-indigo-500 hover:scale-125 transition-transform duration-200" :class="{'ring-2 ring-white scale-110': theme === 'indigo'}"></button>
                    <button @click="setTheme('emerald')" class="w-5 h-5 rounded-full bg-emerald-500 hover:scale-125 transition-transform duration-200" :class="{'ring-2 ring-white scale-110': theme === 'emerald'}"></button>
                    <button @click="setTheme('rose')" class="w-5 h-5 rounded-full bg-rose-500 hover:scale-125 transition-transform duration-200" :class="{'ring-2 ring-white scale-110': theme === 'rose'}"></button>
                    <button @click="setTheme('amber')" class="w-5 h-5 rounded-full bg-amber-500 hover:scale-125 transition-transform duration-200" :class="{'ring-2 ring-white scale-110': theme === 'amber'}"></button>
                    <button @click="setTheme('violet')" class="w-5 h-5 rounded-full bg-violet-500 hover:scale-125 transition-transform duration-200" :class="{'ring-2 ring-white scale-110': theme === 'violet'}"></button>
                </div>
            </div>
        </div>

        <!-- 3 Interactive Stats Cards -->
        @unless($isAnalyticsPage)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 animate-entry delay-200">
            @if(Auth::user()->isAdmin())
            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-indigo-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total User</span>
                    <div class="w-10 h-10 bg-slate-800/80 rounded-xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M12 7a4 4 0 11-8 0 4 4 0 018 0zm8 4a4 4 0 100-8"/></svg>
                    </div>
                </div>
                <p id="user-count" class="text-3xl font-extrabold text-white mt-3 tracking-tight">{{ $userCount }}</p>
            </div>

            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-emerald-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Online</span>
                    <div class="w-10 h-10 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-all duration-300">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    </div>
                </div>
                <p id="online-user-count" class="text-3xl font-extrabold text-emerald-400 mt-3 tracking-tight">{{ $onlineUsers }}</p>
            </div>

            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-amber-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Offline</span>
                    <div class="w-10 h-10 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-400 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p id="offline-user-count" class="text-3xl font-extrabold text-amber-400 mt-3 tracking-tight">{{ $offlineUsers }}</p>
            </div>
            @else
            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-indigo-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Tasks</span>
                    <div class="w-10 h-10 bg-slate-800/80 rounded-xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-white mt-3 tracking-tight">{{ $totalTasks }}</p>
            </div>

            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-amber-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Pending</span>
                    <div class="w-10 h-10 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-400 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-amber-400 mt-3 tracking-tight">{{ $pendingTasks }}</p>
            </div>

            <div class="bg-slate-900/40 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 hover:border-emerald-500/50 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Tuntas</span>
                    <div class="w-10 h-10 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-emerald-400 mt-3 tracking-tight">{{ $completedTasks }}</p>
            </div>
            @endif
        </div>
        @endunless

        @if($isAnalyticsPage)
        <section id="analytics" class="space-y-4 animate-entry delay-200">
            <div>
                <h3 class="text-sm font-extrabold uppercase tracking-widest text-white">Statistik &amp; Analitik</h3>
                <p class="mt-1 text-xs text-slate-400">Statistik database diperbarui otomatis setiap 5 detik.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                <article class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-xl lg:col-span-8">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-white">Tren Aktivitas</h4>
                            <p class="mt-1 text-xs text-slate-400">Aktivitas selama 7 hari terakhir</p>
                        </div>
                        <span class="rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-[11px] font-bold text-indigo-300">7 hari</span>
                    </div>
                    <div class="relative h-64 sm:h-72">
                        <canvas id="activity-trend-chart" aria-label="Grafik tren aktivitas selama tujuh hari" role="img"></canvas>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-xl lg:col-span-4">
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-white">Log Aktivitas Terbaru</h4>
                        <p class="mt-1 text-xs text-slate-400">Pembaruan aktivitas setiap 5 detik</p>
                    </div>
                    <ol id="activity-log-list" class="max-h-80 space-y-5 overflow-y-auto" aria-live="polite">
                        <li class="text-xs text-slate-500">Memuat aktivitas...</li>
                    </ol>
                </article>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
                <article class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-xl">
                    <div class="mb-5 flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-white">Aktivitas Reset Password</h4>
                            <p class="mt-1 text-xs text-slate-400">Jumlah permintaan dalam 7 hari</p>
                        </div>
                        <span id="reset-request-total" class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-lg font-extrabold text-amber-300" aria-live="polite">
                            —
                        </span>
                    </div>
                    <div id="reset-request-chart" class="flex h-28 items-end justify-between gap-2" role="img" aria-label="Grafik permintaan reset password selama tujuh hari">
                        <p class="self-center text-xs text-slate-500">Memuat statistik...</p>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-xl">
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-white">Kategori Task</h4>
                        <p class="mt-1 text-xs text-slate-400">Distribusi task berdasarkan hashtag</p>
                    </div>
                    <div id="task-category-stats" class="space-y-4" aria-live="polite">
                        <p class="text-xs text-slate-500">Memuat statistik...</p>
                    </div>
                    <p id="task-category-total" class="mt-4 text-[11px] text-slate-500">Total task: —</p>
                </article>

                <article class="rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 shadow-2xl backdrop-blur-xl">
                    <div class="mb-5">
                        <h4 class="text-sm font-bold text-white">Level Prioritas</h4>
                        <p class="mt-1 text-xs text-slate-400">Task menurut tingkat prioritas</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-rose-300">High</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500">Prioritas tinggi</p>
                                </div>
                                <span id="priority-high-count" class="text-lg font-extrabold text-white">—</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <div id="priority-high-bar" class="h-full rounded-full bg-rose-400 transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-amber-300">Medium</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500">Prioritas sedang</p>
                                </div>
                                <span id="priority-medium-count" class="text-lg font-extrabold text-white">—</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <div id="priority-medium-bar" class="h-full rounded-full bg-amber-400 transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold text-emerald-300">Low</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500">Prioritas rendah</p>
                                </div>
                                <span id="priority-low-count" class="text-lg font-extrabold text-white">—</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-800">
                                <div id="priority-low-bar" class="h-full rounded-full bg-emerald-400 transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <p id="priority-task-total" class="mt-4 text-[11px] text-slate-500">Total task: —</p>
                </article>
            </div>
        </section>
        @elseif(! Auth::user()->isAdmin())
        <!-- 📊 Progress Bar Completion Widget -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 shadow-2xl animate-entry delay-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Progress Target Selesai</span>
                <span class="text-xs font-extrabold text-indigo-400 font-mono">{{ $progressPercentage }}%</span>
            </div>
            <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 rounded-full transition-all duration-700 ease-out" style="width: {{ $progressPercentage }}%"></div>
            </div>
        </div>
        @endif

        @if(Auth::user()->isAdmin())
        @unless($isAnalyticsPage)
        <section class="flex-1 space-y-5 animate-entry delay-300">
            <section class="overflow-hidden rounded-2xl border border-amber-500/20 bg-slate-900/60 shadow-2xl backdrop-blur-xl">
                <div class="flex flex-col gap-3 border-b border-slate-800/80 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-widest text-white">Request Reset Password</h3>
                        <p class="mt-1 text-xs text-slate-400">Tinjau permintaan dan kirim tautan reset ke email pengguna.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300">{{ $pendingPasswordResetCount }} menunggu</span>
                        <a href="{{ route('admin.password-requests.index') }}" class="text-xs font-bold text-indigo-400 transition hover:text-indigo-300">Semua request →</a>
                    </div>
                </div>
                <div class="divide-y divide-slate-800/60">
                    @forelse($pendingPasswordResetRequests as $resetRequest)
                        <article class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $resetRequest->name ?? 'Nama tidak ditemukan' }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $resetRequest->email }}</p>
                                <time class="mt-1 block text-[11px] text-slate-500">{{ $resetRequest->created_at->format('d M Y, H:i') }}</time>
                            </div>
                            @if($resetRequest->user && ! $resetRequest->user->isAdmin())
                                <form action="{{ route('admin.password-requests.send', $resetRequest) }}" method="POST" onsubmit="return confirm('Kirim link reset ke {{ $resetRequest->email }}?');">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-indigo-500">Setujui &amp; Kirim Link</button>
                                </form>
                            @else
                                <span class="text-xs text-amber-300">Akun member tidak ditemukan</span>
                            @endif
                        </article>
                    @empty
                        <p class="p-8 text-center text-sm text-slate-400">Belum ada request reset password.</p>
                    @endforelse
                </div>
            </section>

            <div class="overflow-hidden rounded-2xl border border-slate-800/80 bg-slate-900/60 shadow-2xl backdrop-blur-xl">
                @if($errors->any())
                    <div class="mx-6 mt-5 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
                @endif
                <div class="flex flex-col gap-3 border-b border-slate-800/80 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-widest text-white">Feedback Menunggu Balasan</h3>
                        <p class="mt-1 text-xs text-slate-400">Balas masukan pengguna langsung dari dashboard.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300">{{ $pendingFeedbackCount }} belum dibalas</span>
                        <a href="{{ route('admin.feedback.index') }}" class="text-xs font-bold text-indigo-400 transition hover:text-indigo-300">Semua feedback →</a>
                    </div>
                </div>

                <div class="divide-y divide-slate-800/60">
                    @forelse($pendingFeedbacks as $index => $feedback)
                        <article class="group p-5 transition duration-300 hover:bg-slate-800/30 md:p-6 animate-entry" style="animation-delay: {{ $index * 100 + 150 }}ms">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/15 text-sm font-extrabold text-indigo-300 transition duration-300 group-hover:rotate-6 group-hover:scale-110">{{ strtoupper(substr($feedback->user->name, 0, 1)) }}</div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $feedback->user->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $feedback->user->email }}</p>
                                    </div>
                                </div>
                                <time class="text-[11px] text-slate-500">{{ $feedback->created_at->format('d M Y, H:i') }}</time>
                            </div>
                            <p class="mb-4 whitespace-pre-wrap break-words text-sm leading-relaxed text-slate-300">{{ $feedback->message }}</p>
                            <form action="{{ route('admin.feedback.reply', $feedback) }}" method="POST" class="space-y-3">
                                @csrf
                                <label for="dashboard-reply-{{ $feedback->id }}" class="sr-only">Balasan untuk feedback dari {{ $feedback->user->name }}</label>
                                <textarea id="dashboard-reply-{{ $feedback->id }}" name="admin_reply" rows="2" maxlength="5000" required placeholder="Tulis balasan untuk {{ $feedback->user->name }}..." class="w-full resize-y rounded-xl border border-slate-700/80 bg-slate-950/80 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">{{ old('admin_reply') }}</textarea>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-indigo-600/20 transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0">
                                        <span>Balas Feedback</span>
                                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </form>
                        </article>
                    @empty
                        <div class="p-12 text-center animate-entry delay-200">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-200">Semua feedback sudah dibalas</p>
                            <p class="mt-1 text-xs text-slate-500">Masukan baru dari pengguna akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        @endunless
        @elseif(! $isAnalyticsPage)
        <!-- Form Input Task -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-5 md:p-6 shadow-2xl animate-entry delay-300">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Buat Task Baru & Target Date</h3>
            
            <form action="{{ route('tasks.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                @csrf
                <div class="md:col-span-4">
                    <input 
                        type="text" 
                        name="title" 
                        placeholder="Nama task / target project..." 
                        required
                        class="w-full bg-slate-950/80 border border-slate-800 text-slate-100 placeholder-slate-600 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-indigo-500 transition-all duration-200"
                    >
                </div>

                <div class="md:col-span-2">
    <select name="category" class="w-full bg-slate-950/80 border border-slate-800 text-slate-300 text-sm font-semibold rounded-xl px-3 py-3.5 focus:outline-none focus:border-indigo-500 cursor-pointer">
        <option value="Sekolah">#Sekolah</option>
        <option value="LSP">#LSP</option>
        <option value="Project" selected>#Project</option>
        <option value="Personal">#Personal</option>
    </select>
</div>

                <div class="md:col-span-3 relative">
    <input 
        type="text" 
        name="due_date" 
        id="due_date_picker"
        placeholder="Pilih Deadline..." 
        required
        readonly
        class="w-full bg-slate-950/80 border border-slate-800 text-slate-200 text-sm rounded-xl px-4 py-3.5 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer shadow-inner"
    >
</div>

                <div class="md:col-span-2">
                    <select 
                        name="priority" 
                        class="w-full bg-slate-950/80 border border-slate-800 text-slate-300 text-sm font-semibold rounded-xl px-3 py-3.5 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer"
                    >
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button 
                        type="submit" 
                        class="w-full text-white font-bold text-sm py-3.5 rounded-xl shadow-lg hover:scale-[1.03] active:scale-95 transition-all duration-200 flex items-center justify-center gap-1.5"
                        :class="{
                            'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/30': theme === 'indigo',
                            'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/30': theme === 'emerald',
                            'bg-rose-600 hover:bg-rose-500 shadow-rose-600/30': theme === 'rose',
                            'bg-amber-600 hover:bg-amber-500 shadow-amber-600/30': theme === 'amber',
                            'bg-violet-600 hover:bg-violet-500 shadow-violet-600/30': theme === 'violet'
                        }"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Task List Area (Realtime Filtering by Alpine.js) -->
        <div class="flex-1 bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-entry delay-400">
            <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Task & Target Goals</span>
                <span class="text-xs text-slate-500" x-text="searchQuery ? 'Menampilkan hasil pencarian...' : 'Klik checkbox untuk merayakan task selesai!'"></span>
            </div>

            

            <div class="divide-y divide-slate-800/50 overflow-y-auto">
                @forelse($tasks as $index => $task)
                @php
                        $dueDate = \Carbon\Carbon::parse($task->due_date);
                        $isOverdue = !$task->is_completed && $dueDate->isPast() && !$dueDate->isToday();
                        $isToday = !$task->is_completed && $dueDate->isToday();
                    @endphp
                    <div 
                        x-show="searchQuery === '' || '{{ strtolower(addslashes($task->title)) }}'.includes(searchQuery.toLowerCase())"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="p-4 md:px-6 flex items-center justify-between hover:bg-slate-800/50 hover:scale-[1.008] transition-all duration-300 group animate-entry"
                        style="animation-delay: {{ $index * 50 + 200 }}ms"
                    >
                        
                        <!-- Checkbox & Task Title -->
                        <form action="{{ route('tasks.update', $task) }}" method="POST" class="flex-1 flex items-center gap-4" @submit="if(!{{ $task->is_completed ? 'true' : 'false' }}) { triggerConfetti(); }">
    @csrf
    @method('PATCH')
    
    <!-- Penanda bahwa ini adalah toggle status dari checkbox -->
    <input type="hidden" name="toggle_status" value="1">

    <button type="submit" class="flex items-center gap-4 text-left w-full group/btn">
        <div class="w-6 h-6 rounded-lg border transition-all duration-300 flex items-center justify-center shrink-0 group-hover/btn:scale-125"
             :class="{{ $task->is_completed ? 'true' : 'false' }} ? 'bg-gradient-to-tr from-indigo-600 to-violet-600 border-indigo-500 text-white shadow-lg shadow-indigo-600/40' : 'border-slate-700 bg-slate-950 group-hover/btn:border-indigo-500'">
            @if($task->is_completed)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>

                                <div>
    <!-- Judul Task & Tag Kategori -->
    <div class="flex items-center gap-2">
        <span class="text-sm font-semibold transition-all duration-300 {{ $task->is_completed ? 'line-through text-slate-500 italic' : 'text-slate-200 group-hover/btn:text-indigo-400 group-hover/btn:translate-x-1' }}">
            {{ $task->title }}
        </span>
        <!-- Tag Kategori (#Sekolah, #LSP, #Project, dll) -->
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-800 text-indigo-400 border border-slate-700">
            #{{ $task->category ?? 'General' }}
        </span>
    </div>

    <!-- Smart Deadline Status Badge -->
    <div class="flex items-center gap-2 mt-1 text-[11px]">
        <span class="text-slate-400">Target Goal: {{ $dueDate->format('d M Y') }}</span>
        
        @if($isOverdue)
            <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 border border-rose-500/30">🔴 OVERDUE</span>
        @elseif($isToday)
            <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">🟡 HARI INI</span>
        @else
            <span class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30">🔵 MENDATANG</span>
        @endif
    </div>
</div>
                            </button>
                        </form>

                        <!-- Priority & Delete Button -->
                        <div class="flex items-center gap-3 shrink-0">
                            @if($task->priority == 'High')
                                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 shadow-sm animate-pulse">HIGH</span>
                            @elseif($task->priority == 'Medium')
                                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-sm">MEDIUM</span>
                            @else
                                <span class="text-[10px] font-extrabold px-3 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700">LOW</span>
                            @endif

                            <!-- Tombol Trigger Modal Edit -->
                            <button type="button" @click="openEditModal({ id: '{{ $task->id }}', title: '{{ addslashes($task->title) }}', category: '{{ $task->category ?? 'General' }}', priority: '{{ $task->priority }}', due_date: '{{ $task->due_date }}' })" 
                                    class="p-2 text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-xl transition-all" title="Edit Task">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form id="delete-form-{{ $task->id }}" action="{{ route('tasks.destroy', $task) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete('{{ $task->id }}', '{{ addslashes($task->title) }}')" class="opacity-30 group-hover:opacity-100 p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all duration-200 hover:scale-125 active:scale-95" title="Hapus Task">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-16 text-center text-slate-500 animate-entry">
                        <p class="text-sm font-bold text-slate-400">Belum Ada Target / Task</p>
                        <p class="text-xs text-slate-600 mt-1">Tambahkan task baru di atas untuk memulai!</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

    </main>
    <!-- ✏️ Inline Edit Task Modal -->
    @if(!Auth::user()->isAdmin())
    <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="editModalOpen = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative">
            <h3 class="text-base font-bold text-white mb-4">Edit Task & Target Goal</h3>
            <form :action="`/tasks/${editTaskData.id}`" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-xs text-slate-400 font-bold uppercase mb-1 block">Judul Task</label>
                    <input type="text" name="title" x-model="editTaskData.title" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-400 font-bold uppercase mb-1 block">Kategori</label>
                        <select name="category" x-model="editTaskData.category" class="w-full bg-slate-950 border border-slate-800 text-slate-300 text-sm rounded-xl px-3 py-3 focus:outline-none">
                            <option value="Sekolah">#Sekolah</option>
                            <option value="LSP">#LSP</option>
                            <option value="Project">#Project</option>
                            <option value="Personal">#Personal</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 font-bold uppercase mb-1 block">Priority</label>
                        <select name="priority" x-model="editTaskData.priority" class="w-full bg-slate-950 border border-slate-800 text-slate-300 text-sm rounded-xl px-3 py-3 focus:outline-none">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-slate-400 font-bold uppercase mb-1 block">Deadline</label>
                    <input type="text" name="due_date" id="edit_due_date_picker" x-model="editTaskData.due_date" required readonly class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-4 py-3 text-sm focus:outline-none cursor-pointer">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 rounded-xl text-slate-400 text-xs font-bold hover:bg-slate-800">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        const customSwal = Swal.mixin({
            background: '#0f172a',
            color: '#f8fafc',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#334155',
            customClass: { popup: 'border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl' }
        });

        function confirmDelete(id, title) {
            customSwal.fire({
                title: 'Hapus Task Ini?',
                html: `Yakin hapus <span class="text-rose-400 font-semibold">"${title}"</span>?`,
                icon: 'warning',
                iconColor: '#f43f5e',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        }
    </script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#0f172a',
            color: '#f8fafc',
            customClass: { popup: 'border border-slate-800 rounded-xl shadow-xl' }
        });

        @if(session('success'))
            Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
        @endif
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr("#due_date_picker", {
            locale: "id",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j F Y",
            minDate: "today",
            animate: true,
            disableMobile: "true"
        });
    });
</script>
<script>
    @if(Auth::user()->isAdmin())
        const presenceUrl = @json(route('admin.user-presence'));
        const refreshPresence = () => fetch(presenceUrl, { credentials: 'same-origin' })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memperbarui status online pengguna.');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('user-count').textContent = data.userCount;
                document.getElementById('online-user-count').textContent = data.onlineUsers;
                document.getElementById('offline-user-count').textContent = data.offlineUsers;
            })
            .catch(error => console.error(error));
        window.setInterval(refreshPresence, 60000);
    @endif
</script>
@if($isAnalyticsPage)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('activity-trend-chart');
        const activityList = document.getElementById('activity-log-list');
        if (!canvas || !activityList) return;

        const context = canvas.getContext('2d');
        const fill = context.createLinearGradient(0, 0, 0, 280);
        fill.addColorStop(0, 'rgba(99, 102, 241, 0.32)');
        fill.addColorStop(1, 'rgba(99, 102, 241, 0)');

        const activityChart = new Chart(context, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Aktivitas',
                    data: [],
                    borderColor: '#818cf8',
                    backgroundColor: fill,
                    borderWidth: 2,
                    pointBackgroundColor: '#a5b4fc',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        borderColor: '#334155',
                        borderWidth: 1,
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans' } },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51, 65, 85, 0.45)' },
                        ticks: { color: '#64748b', precision: 0, font: { family: 'Plus Jakarta Sans' } },
                        border: { display: false }
                    }
                }
            }
        });

        const activityUrl = @json(route('admin.activity-analytics'));
        const relativeTime = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });
        const timeAgo = (value) => {
            const seconds = Math.round((new Date(value).getTime() - Date.now()) / 1000);
            const units = [
                ['year', 31536000],
                ['month', 2592000],
                ['week', 604800],
                ['day', 86400],
                ['hour', 3600],
                ['minute', 60]
            ];

            for (const [unit, size] of units) {
                if (Math.abs(seconds) >= size) {
                    return relativeTime.format(Math.round(seconds / size), unit);
                }
            }

            return relativeTime.format(seconds, 'second');
        };

        const activityStyles = {
            created: 'bg-indigo-500/10 text-indigo-300',
            completed: 'bg-emerald-500/10 text-emerald-300',
            reopened: 'bg-amber-500/10 text-amber-300',
            updated: 'bg-sky-500/10 text-sky-300',
            deleted: 'bg-rose-500/10 text-rose-300'
        };
        const categoryBarStyles = [
            'bg-indigo-400',
            'bg-violet-400',
            'bg-cyan-400',
            'bg-emerald-400',
            'bg-amber-400',
            'bg-rose-400'
        ];

        const renderResetRequests = (resetRequests) => {
            const chart = document.getElementById('reset-request-chart');
            document.getElementById('reset-request-total').textContent = resetRequests.total;
            chart.replaceChildren();

            const maxCount = Math.max(...resetRequests.data, 1);
            resetRequests.labels.forEach((label, index) => {
                const column = document.createElement('div');
                column.className = 'flex h-full flex-1 flex-col items-center justify-end gap-2';

                const count = document.createElement('span');
                count.className = 'text-[10px] font-semibold text-slate-400';
                count.textContent = resetRequests.data[index];

                const bar = document.createElement('div');
                bar.className = 'w-full max-w-8 rounded-t-md bg-gradient-to-t from-amber-600/70 to-amber-300 transition-all';
                bar.style.height = `${Math.max(8, (resetRequests.data[index] / maxCount) * 100)}%`;

                const dayLabel = document.createElement('span');
                dayLabel.className = 'text-[10px] text-slate-500';
                dayLabel.textContent = label;

                column.append(count, bar, dayLabel);
                chart.append(column);
            });
        };

        const renderCategories = (categories) => {
            const container = document.getElementById('task-category-stats');
            const total = categories.reduce((sum, category) => sum + category.count, 0);
            container.replaceChildren();
            document.getElementById('task-category-total').textContent = `Total task: ${total}`;

            if (categories.length === 0) {
                const emptyMessage = document.createElement('p');
                emptyMessage.className = 'text-xs text-slate-500';
                emptyMessage.textContent = 'Belum ada task.';
                container.append(emptyMessage);
                return;
            }

            categories.forEach((category, index) => {
                const row = document.createElement('div');
                const heading = document.createElement('div');
                heading.className = 'mb-2 flex items-center justify-between gap-3 text-xs';

                const name = document.createElement('span');
                name.className = 'font-semibold text-slate-200';
                name.textContent = `#${category.name}`;

                const value = document.createElement('span');
                value.className = 'text-slate-400';
                value.textContent = `${category.count} task (${category.percentage}%)`;
                heading.append(name, value);

                const track = document.createElement('div');
                track.className = 'h-2 overflow-hidden rounded-full bg-slate-800';

                const bar = document.createElement('div');
                bar.className = `h-full rounded-full transition-all duration-500 ${categoryBarStyles[index % categoryBarStyles.length]}`;
                bar.style.width = `${category.percentage}%`;
                track.append(bar);
                row.append(heading, track);
                container.append(row);
            });
        };

        const renderPriorities = (priorities) => {
            const levels = [
                ['high', priorities.high],
                ['medium', priorities.medium],
                ['low', priorities.low]
            ];
            const maxCount = Math.max(...levels.map(([, count]) => count), 1);

            levels.forEach(([level, count]) => {
                document.getElementById(`priority-${level}-count`).textContent = count;
                document.getElementById(`priority-${level}-bar`).style.width = `${(count / maxCount) * 100}%`;
            });

            document.getElementById('priority-task-total').textContent = `Total task: ${priorities.total}`;
        };

        const renderActivities = (activities) => {
            activityList.replaceChildren();

            if (activities.length === 0) {
                const emptyItem = document.createElement('li');
                emptyItem.className = 'text-xs text-slate-500';
                emptyItem.textContent = 'Belum ada aktivitas.';
                activityList.append(emptyItem);
                return;
            }

            activities.forEach((activity) => {
                const item = document.createElement('li');
                item.className = 'flex gap-3';

                const marker = document.createElement('span');
                marker.className = `flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${activityStyles[activity.type] || 'bg-slate-800 text-slate-300'}`;
                marker.setAttribute('aria-hidden', 'true');
                marker.textContent = activity.type === 'completed' ? '✓' : activity.type === 'deleted' ? '×' : '•';

                const content = document.createElement('div');
                content.className = 'min-w-0 flex-1';

                const description = document.createElement('p');
                description.className = 'text-xs leading-relaxed text-slate-200';
                const userName = document.createElement('span');
                userName.className = 'font-bold text-white';
                userName.textContent = activity.name;
                description.append(userName, document.createTextNode(` ${activity.action}`));

                const subject = document.createElement('p');
                subject.className = 'mt-1 truncate text-[11px] text-slate-500';
                subject.textContent = activity.subject || '';

                const time = document.createElement('time');
                time.className = 'mt-1 block text-[10px] text-slate-500';
                time.dateTime = activity.createdAt;
                time.textContent = timeAgo(activity.createdAt);

                content.append(description, subject, time);
                item.append(marker, content);
                activityList.append(item);
            });
        };

        let isRefreshing = false;
        const refreshActivityAnalytics = async () => {
            if (isRefreshing) return;
            isRefreshing = true;

            try {
                const response = await fetch(activityUrl, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' }
                });
                if (!response.ok) {
                    throw new Error('Gagal memuat statistik dan log aktivitas.');
                }

                const data = await response.json();
                activityChart.data.labels = data.trend.labels;
                activityChart.data.datasets[0].data = data.trend.data;
                activityChart.update();
                renderResetRequests(data.resetRequests);
                renderActivities(data.activities);
                renderCategories(data.categories);
                renderPriorities(data.priorities);
            } catch (error) {
                console.error(error);
            } finally {
                isRefreshing = false;
            }
        };

        refreshActivityAnalytics();
        window.setInterval(refreshActivityAnalytics, 5000);
    });
</script>
@endif

<style>
    /* Mengatur Lebar, Padding, dan Align Kalender Flatpickr */
    .flatpickr-calendar {
        width: 320px !important; /* Dilebarkan dari 280px ke 320px biar angka kanan ga terpotong */
        margin-top: 12px !important;
        background: #0f172a !important;
        border: 1px solid rgba(51, 65, 85, 0.8) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
        border-radius: 1rem !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        padding: 12px !important;
    }

    /* Memastikan container hari dapat ruang lebih lega */
    .flatpickr-days, .dayContainer {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }

    .flatpickr-day {
        max-width: 38px !important;
        height: 38px !important;
        line-height: 38px !important;
    }
    
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: linear-gradient(to right, #4f46e5, #7c3aed) !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.5) !important;
        font-weight: bold !important;
    }

    .flatpickr-day:hover {
        background: #1e293b !important;
    }

    .flatpickr-day.today {
        border-color: #6366f1 !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: #0f172a !important;
    }
</style>
</body>
</html>