<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="TaskSpace membantu kamu mengatur tugas, menentukan prioritas, dan memantau deadline.">
    <title>TaskSpace — Atur Prioritas. Selesaikan Lebih Banyak.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes appear { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        .hero-orb { animation: float 8s ease-in-out infinite; }
        .landing-enter { animation: appear .7s ease-out both; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; scroll-behavior: auto !important; } }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#070b14] text-slate-100 antialiased selection:bg-violet-500/30">
    <div class="pointer-events-none fixed -left-40 -top-40 h-[34rem] w-[34rem] rounded-full bg-indigo-600/20 blur-[120px]"></div>
    <div class="hero-orb pointer-events-none fixed -right-36 top-1/3 h-[30rem] w-[30rem] rounded-full bg-violet-600/15 blur-[120px]"></div>
    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
        <a href="{{ route('tasks.index') }}" class="flex items-center gap-3" aria-label="TaskSpace">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-blue-600 text-lg font-black text-white shadow-lg shadow-violet-600/25">✓</span>
            <span class="text-lg font-extrabold tracking-tight text-white">TaskSpace</span>
        </a>
        <nav class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-300 transition hover:bg-slate-800 hover:text-white">Login</a>
            <a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Mulai Sekarang</a>
        </nav>
    </header>

    <main class="relative z-10">
        <section class="mx-auto grid min-h-[74vh] max-w-7xl items-center gap-12 px-5 py-20 sm:px-8 lg:grid-cols-[1.05fr_.95fr] lg:py-28">
            <div class="landing-enter">
                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-4 py-2 text-[11px] font-extrabold uppercase tracking-[.18em] text-indigo-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Workspace tugas yang lebih teratur
                </span>
                <h1 class="mt-7 max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-6xl">Atur Prioritas.<br><span class="bg-gradient-to-r from-indigo-300 via-violet-300 to-blue-300 bg-clip-text text-transparent">Selesaikan Lebih Banyak.</span></h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-400 sm:text-lg">TaskSpace membantu kamu mengatur tugas, menentukan prioritas, memantau deadline, dan menyelesaikan pekerjaan dengan lebih terorganisasi.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3.5 text-sm font-extrabold text-white shadow-xl shadow-indigo-950/50 transition hover:-translate-y-0.5 hover:from-indigo-500 hover:to-violet-500">Mulai Sekarang <span aria-hidden="true">→</span></a>
                    <a href="{{ route('login') }}" class="rounded-xl border border-slate-700 bg-slate-900/70 px-6 py-3.5 text-sm font-bold text-slate-200 transition hover:border-indigo-500/50 hover:bg-slate-800">Login</a>
                </div>
                <p class="mt-5 text-xs text-slate-500">Mulai kelola pekerjaanmu dalam satu ruang kerja pribadi.</p>
            </div>

            <div class="landing-enter relative mx-auto w-full max-w-xl [animation-delay:120ms]">
                <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-indigo-500/20 to-violet-500/10 blur-2xl"></div>
                <div class="relative rounded-3xl border border-slate-700/80 bg-slate-900/80 p-5 shadow-2xl shadow-black/40 backdrop-blur-xl sm:p-7">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-5">
                        <div><p class="text-xs font-bold text-slate-400">WORKSPACE</p><p class="mt-1 text-lg font-extrabold text-white">Fokus hari ini</p></div>
                        <span class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-bold text-emerald-300">Tetap terorganisasi</span>
                    </div>
                    <div class="mt-5 space-y-3">
                        @foreach([
                            ['Selesaikan proposal proyek', 'HIGH', 'rose', true],
                            ['Review materi presentasi', 'MEDIUM', 'amber', false],
                            ['Kirim dokumen ke tim', 'LOW', 'blue', false],
                        ] as [$title, $priority, $color, $done])
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border {{ $done ? 'border-indigo-500 bg-indigo-600 text-white' : 'border-slate-700 text-transparent' }}">✓</span>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold {{ $done ? 'text-slate-500 line-through' : 'text-slate-200' }}">{{ $title }}</span>
                                <span class="rounded-md bg-{{ $color }}-500/10 px-2 py-1 text-[9px] font-extrabold text-{{ $color }}-300">{{ $priority }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 flex items-center gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-300">◷</span>
                        <div><p class="text-xs font-bold text-amber-200">Pengingat Deadline!</p><p class="mt-1 text-[10px] text-slate-400">Tugas presentasi jatuh tempo hari ini.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="fitur" class="border-y border-slate-800/70 bg-slate-900/35 px-5 py-20 sm:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-[11px] font-extrabold uppercase tracking-[.2em] text-indigo-300">Semua yang kamu butuhkan</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Satu ruang untuk semua pekerjaan</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-400">Atur tugas dari awal hingga selesai dengan fitur yang dirancang untuk membantu kamu tetap fokus.</p>
                </div>
                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        ['Task Management', 'Kelola seluruh pekerjaan dalam satu tempat.', '✓', 'indigo'],
                        ['Priority Management', 'Tentukan tugas berdasarkan tingkat prioritas.', '↗', 'rose'],
                        ['Deadline Tracking', 'Pantau tenggat waktu setiap pekerjaan.', '◷', 'amber'],
                        ['Smart Reminder', 'Dapatkan pengingat ketika deadline tiba.', '♧', 'violet'],
                        ['File Attachment', 'Lampirkan dokumen dan file pendukung pada tugas.', '⌁', 'blue'],
                        ['Progress Tracking', 'Pantau perkembangan pekerjaan hingga selesai.', '▤', 'emerald'],
                    ] as [$title, $description, $icon, $color])
                        <article class="rounded-2xl border border-slate-800 bg-slate-950/60 p-6 transition duration-300 hover:-translate-y-1 hover:border-{{ $color }}-500/40">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-{{ $color }}-500/10 text-lg font-extrabold text-{{ $color }}-300">{{ $icon }}</span>
                            <h3 class="mt-5 text-base font-extrabold text-white">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-20 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-[11px] font-extrabold uppercase tracking-[.2em] text-violet-300">Cara kerja</span>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Mulai dalam tiga langkah</h2>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach([
                    ['01', 'Buat tugas', 'Catat semua pekerjaan yang ingin kamu selesaikan.'],
                    ['02', 'Tentukan prioritas dan deadline', 'Atur fokus dan tenggat agar setiap target lebih jelas.'],
                    ['03', 'Selesaikan tugas dan pantau progres', 'Tandai tugas selesai dan lihat perkembanganmu.'],
                ] as [$number, $title, $description])
                    <article class="rounded-2xl border border-slate-800 bg-slate-900/55 p-6">
                        <span class="text-sm font-extrabold tracking-widest text-indigo-300">{{ $number }}</span>
                        <h3 class="mt-4 text-lg font-extrabold text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="px-5 pb-20 sm:px-8">
            <div class="mx-auto max-w-7xl overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950/80 via-slate-900 to-violet-950/60 px-6 py-14 text-center sm:px-12">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Mulai Atur Pekerjaanmu Hari Ini.</h2>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-300">Buat ruang kerja pribadi, susun prioritas, dan selesaikan pekerjaan dengan lebih terorganisasi.</p>
                <a href="{{ route('register') }}" class="mt-7 inline-flex rounded-xl bg-indigo-600 px-6 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-indigo-950/50 transition hover:bg-indigo-500">Buat Akun Gratis <span class="ml-2" aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-800/80 bg-slate-950/50 px-5 py-8 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-md">
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-2 text-sm font-extrabold text-white"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-blue-600">✓</span>TaskSpace</a>
                <p class="mt-2 text-xs leading-5 text-slate-500">Atur prioritas, pantau deadline, dan selesaikan pekerjaan dalam satu ruang kerja.</p>
            </div>
            <nav class="flex gap-5 text-xs font-bold text-slate-400">
                <a href="{{ route('login') }}" class="transition hover:text-white">Login</a>
                <a href="{{ route('register') }}" class="transition hover:text-white">Register</a>
            </nav>
            <p class="text-xs text-slate-600">&copy; {{ now()->year }} TaskSpace</p>
        </div>
    </footer>
</body>
</html>
