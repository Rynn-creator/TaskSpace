<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — TaskSpace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes adminFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes adminSlideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        .admin-page { animation: adminFadeIn 260ms ease-out both; }
        .admin-card { animation: adminSlideUp 420ms cubic-bezier(.2,.8,.2,1) both; }
        .admin-nav-link { transition: color 180ms ease, background-color 180ms ease, border-color 180ms ease, transform 180ms ease; }
        .admin-nav-link:hover { transform: translateX(2px); }
        .admin-surface { border: 1px solid rgba(51, 65, 85, .72); background: rgba(15, 23, 42, .68); box-shadow: 0 16px 42px rgba(2, 6, 23, .18); }
        @keyframes chartTooltipFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        .chart-tooltip { position: fixed; z-index: 100; max-width: min(230px, calc(100vw - 24px)); pointer-events: none; border: 1px solid rgba(99, 102, 241, .35); border-radius: .75rem; background: rgba(8, 13, 27, .97); padding: .65rem .8rem; color: #e2e8f0; box-shadow: 0 12px 30px rgba(0, 0, 0, .45); opacity: 0; visibility: hidden; }
        .chart-tooltip.is-visible { visibility: visible; animation: chartTooltipFadeIn 140ms ease-out both; }
        .chart-bar-hitbox { position: relative; cursor: pointer; }
        .chart-bar-hitbox::before { position: absolute; inset: -4px -1px; content: ''; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .admin-nav-link:hover { transform: none; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#070b14] text-slate-100 antialiased selection:bg-violet-500/30">
    <div class="min-h-screen md:flex">
        @include('partials.sidebar')
        <main class="admin-page min-w-0 flex-1 p-5 sm:p-7 lg:p-10">
            <div class="mx-auto max-w-7xl space-y-6">
                @yield('content')
            </div>
        </main>
    </div>
    <script>
        (() => {
            const root = document.querySelector('[data-live-page]');
            if (!root) return;

            const status = root.querySelector('[data-live-status]');
            const dot = root.querySelector('[data-live-dot]');
            const updated = root.querySelector('[data-live-updated]');
            const refreshButton = root.querySelector('[data-live-refresh]');
            const numberFormat = new Intl.NumberFormat('id-ID');
            const seriesLabels = { users: 'Pengguna baru', tasks: 'Tugas dibuat', completed: 'Tugas selesai' };
            let timer = null;
            let inFlight = false;
            let activitySignature = '';
            let activeTooltipBar = null;
            let tooltip = null;

            const setStatus = (label, state) => {
                status.textContent = label;
                dot.className = `h-2 w-2 rounded-full ${state === 'live' ? 'bg-emerald-400' : state === 'updating' ? 'animate-pulse bg-blue-400' : state === 'error' ? 'bg-rose-400' : 'bg-slate-500'}`;
                status.className = `font-bold ${state === 'live' ? 'text-emerald-300' : state === 'updating' ? 'text-blue-300' : state === 'error' ? 'text-rose-300' : 'text-slate-400'}`;
            };

            const renderActivities = (activities) => {
                const container = root.querySelector('[data-live-activities]');
                if (!container) return;

                const signature = JSON.stringify(activities);
                if (signature === activitySignature) return;
                activitySignature = signature;
                container.replaceChildren();
                const pagination = root.querySelector('[data-activity-pagination]');
                if (pagination) pagination.classList.toggle('hidden', activities.length === 0);

                if (activities.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = root.dataset.livePage === 'dashboard'
                        ? 'py-8 text-center text-sm text-slate-500'
                        : 'p-8 text-center text-sm text-slate-500';
                    empty.textContent = container.dataset.emptyMessage;
                    container.append(empty);
                    return;
                }

                activities.forEach((activity) => {
                    const row = document.createElement(root.dataset.livePage === 'dashboard' ? 'div' : 'article');
                    row.className = root.dataset.livePage === 'dashboard'
                        ? 'flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between'
                        : 'flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between';

                    const copy = document.createElement('div');
                    if (root.dataset.livePage === 'dashboard') {
                        copy.className = 'flex min-w-0 items-start gap-3';
                        const marker = document.createElement('span');
                        marker.className = `mt-1.5 h-2 w-2 shrink-0 rounded-full ${activity.type === 'completed' ? 'bg-emerald-400' : 'bg-violet-400'}`;
                        copy.append(marker);
                    }

                    const description = document.createElement('p');
                    description.className = 'text-sm text-slate-200';
                    const name = document.createElement('span');
                    name.className = 'font-bold text-white';
                    name.textContent = activity.name;
                    description.append(name, document.createTextNode(` ${activity.action}`));
                    copy.append(description);

                    if (activity.subject) {
                        const subject = document.createElement('p');
                        subject.className = root.dataset.livePage === 'dashboard'
                            ? 'mt-1 truncate text-xs text-slate-500'
                            : 'mt-1 text-xs text-slate-500';
                        subject.textContent = activity.subject;
                        copy.append(subject);
                    }
                    row.append(copy);

                    const time = document.createElement('time');
                    time.className = 'shrink-0 text-xs text-slate-500';
                    time.dateTime = activity.createdAt;
                    time.textContent = activity.createdAtLabel;
                    row.append(time);
                    container.append(row);
                });
            };

            const updateTooltip = (bar) => {
                if (!tooltip || !bar) return;
                const title = tooltip.querySelector('[data-tooltip-title]');
                const date = tooltip.querySelector('[data-tooltip-date]');
                const value = tooltip.querySelector('[data-tooltip-value]');
                const series = bar.dataset.series;
                const day = bar.closest('.live-trend-day');
                title.textContent = seriesLabels[series];
                date.textContent = day.dataset.label;
                value.textContent = numberFormat.format(Number(bar.dataset.tooltipValue));
                bar.setAttribute('aria-label', `${title.textContent}, ${date.textContent}: ${value.textContent}`);
            };

            const positionTooltip = (x, y) => {
                if (!tooltip) return;
                const gap = 12;
                const bounds = tooltip.getBoundingClientRect();
                const left = Math.max(8, Math.min(x + gap, window.innerWidth - bounds.width - 8));
                const top = y - bounds.height - gap >= 8
                    ? y - bounds.height - gap
                    : Math.min(y + gap, window.innerHeight - bounds.height - 8);
                tooltip.style.left = `${left}px`;
                tooltip.style.top = `${Math.max(8, top)}px`;
            };

            const showTooltip = (bar, x, y) => {
                activeTooltipBar = bar;
                updateTooltip(bar);
                tooltip.classList.add('is-visible');
                positionTooltip(x, y);
            };

            if (root.dataset.livePage === 'statistics') {
                tooltip = document.createElement('div');
                tooltip.id = 'statistics-chart-tooltip';
                tooltip.className = 'chart-tooltip';
                tooltip.setAttribute('role', 'tooltip');
                tooltip.innerHTML = '<p data-tooltip-title class="text-xs font-bold text-violet-200"></p><p data-tooltip-date class="mt-1 text-[10px] text-slate-400"></p><p class="mt-1 text-sm font-extrabold text-white"><span data-tooltip-value></span></p>';
                document.body.append(tooltip);

                root.querySelectorAll('[data-series]').forEach((bar) => {
                    bar.setAttribute('aria-describedby', tooltip.id);
                    bar.addEventListener('mouseenter', (event) => showTooltip(bar, event.clientX, event.clientY));
                    bar.addEventListener('mousemove', (event) => positionTooltip(event.clientX, event.clientY));
                    bar.addEventListener('mouseleave', () => {
                        tooltip.classList.remove('is-visible');
                        activeTooltipBar = null;
                    });
                    bar.addEventListener('focus', () => {
                        const bounds = bar.getBoundingClientRect();
                        showTooltip(bar, bounds.left + bounds.width / 2, bounds.top);
                    });
                    bar.addEventListener('blur', () => {
                        tooltip.classList.remove('is-visible');
                        activeTooltipBar = null;
                    });
                });
            }

            const updateDashboard = (data) => {
                Object.entries(data.stats).forEach(([key, value]) => {
                    const element = root.querySelector(`[data-stat="${key}"]`);
                    if (element) element.textContent = numberFormat.format(value);
                });
                renderActivities(data.activities);
            };

            const updateStatistics = (data) => {
                const days = [...root.querySelectorAll('.live-trend-day')];
                const maxValue = Math.max(1, ...data.trend.users, ...data.trend.tasks, ...data.trend.completed);
                days.forEach((day, index) => {
                    const seriesValues = {
                        users: data.trend.users[index] ?? 0,
                        tasks: data.trend.tasks[index] ?? 0,
                        completed: data.trend.completed[index] ?? 0,
                    };
                    Object.entries(seriesValues).forEach(([series, value]) => {
                        const bar = day.querySelector(`[data-series="${series}"]`);
                        if (!bar) return;
                        bar.style.height = `${value > 0 ? Math.max(4, (value / maxValue) * 100) : 1}%`;
                        bar.dataset.tooltipValue = value;
                        bar.setAttribute('aria-label', `${bar.dataset.tooltipLabel}, ${day.dataset.label}: ${numberFormat.format(value)}`);
                        if (bar === activeTooltipBar) updateTooltip(bar);
                    });
                });

                const priorityTotal = Object.values(data.priorities).reduce((sum, value) => sum + value, 0);
                Object.entries(data.priorities).forEach(([key, value]) => {
                    const percent = priorityTotal ? Math.round((value / priorityTotal) * 100) : 0;
                    const count = root.querySelector(`[data-priority-value="${key}"]`);
                    const label = root.querySelector(`[data-priority-percent="${key}"]`);
                    const bar = root.querySelector(`[data-priority-bar="${key}"]`);
                    if (count) count.textContent = numberFormat.format(value);
                    if (label) label.textContent = `(${percent}%)`;
                    if (bar) bar.style.width = `${percent}%`;
                });

                Object.entries(data.status).forEach(([key, value]) => {
                    const percent = data.totalTasks ? Math.round((value / data.totalTasks) * 100) : 0;
                    const count = root.querySelector(`[data-status-value="${key}"]`);
                    const bar = root.querySelector(`[data-status-bar="${key}"]`);
                    if (count) count.textContent = numberFormat.format(value);
                    if (bar) bar.style.width = `${percent}%`;
                });
                const overdue = root.querySelector('[data-overdue-count]');
                if (overdue) overdue.textContent = numberFormat.format(data.overdueTasks);
                renderActivities(data.activities);
            };

            const refresh = async () => {
                if (inFlight || document.visibilityState !== 'visible') return;
                inFlight = true;
                setStatus('Memperbarui data', 'updating');

                try {
                    const response = await fetch(root.dataset.liveUrl, {
                        credentials: 'same-origin',
                        headers: { Accept: 'application/json' },
                        cache: 'no-store',
                    });
                    if (!response.ok) throw new Error(`Permintaan data gagal (${response.status}).`);

                    const data = await response.json();
                    if (root.dataset.livePage === 'dashboard') updateDashboard(data);
                    else updateStatistics(data);

                    updated.textContent = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    }).format(new Date(data.updatedAt));
                    setStatus('Live', 'live');
                } catch (error) {
                    console.error('Pembaruan data admin gagal.', error);
                    setStatus('Gagal terhubung', 'error');
                } finally {
                    inFlight = false;
                    if (document.visibilityState === 'visible') {
                        timer = window.setTimeout(refresh, 12000);
                    }
                }
            };

            refreshButton.addEventListener('click', () => {
                window.clearTimeout(timer);
                refresh();
            });
            document.addEventListener('visibilitychange', () => {
                window.clearTimeout(timer);
                if (document.visibilityState === 'visible') refresh();
            });
            refresh();
        })();
    </script>
</body>
</html>
