<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PasswordResetRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('admin.dashboard', $this->dashboardData());
    }

    public function liveDashboard(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $this->dashboardData();

        return response()->json([
            'updatedAt' => now()->toIso8601String(),
            'stats' => [
                'totalUsers' => $data['totalUsers'],
                'activeUsers' => $data['activeUsers'],
                'onlineUsers' => $data['onlineUsers'],
                'totalTasks' => $data['totalTasks'],
                'completedTasks' => $data['completedTasks'],
                'pendingTasks' => $data['pendingTasks'],
                'overdueTasks' => $data['overdueTasks'],
            ],
            'activities' => $this->formatActivities($data['activities']),
        ]);
    }

    public function liveStatistics(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $request->validate(['page' => ['sometimes', 'integer', 'min:1']]);

        $data = $this->statisticsData();

        return response()->json([
            'updatedAt' => now()->toIso8601String(),
            'trend' => [
                'labels' => $data['labels'],
                'users' => $data['userTrend'],
                'tasks' => $data['taskTrend'],
                'completed' => $data['completedTrend'],
            ],
            'priorities' => [
                'High' => (int) ($data['priorityCounts']['High'] ?? 0),
                'Medium' => (int) ($data['priorityCounts']['Medium'] ?? 0),
                'Low' => (int) ($data['priorityCounts']['Low'] ?? 0),
            ],
            'status' => $data['statusCounts'],
            'totalTasks' => $data['totalTasks'],
            'overdueTasks' => $data['overdueTasks'],
            'activities' => $this->formatActivities($data['activities']->getCollection()),
        ]);
    }

    private function dashboardData(): array
    {
        $onlineSince = now()->subMinutes(5);
        $users = User::query()->where('role', 'user');
        $totalUsers = (clone $users)->count();
        $activeUsers = (clone $users)->where('is_suspended', false)->count();
        $onlineUsers = (clone $users)
            ->where('is_suspended', false)
            ->whereIn('id', function ($query) use ($onlineSince) {
                $query->select('user_id')
                    ->from('user_presence')
                    ->where('last_seen_at', '>=', $onlineSince);
            })
            ->count();

        $tasks = Task::query();
        $totalTasks = (clone $tasks)->count();
        $completedTasks = (clone $tasks)->where('is_completed', true)->count();
        $pendingTasks = (clone $tasks)->where('is_completed', false)->count();
        $overdueTasks = (clone $tasks)
            ->where('is_completed', false)
            ->whereDate('due_date', '<', today())
            ->count();
        $activities = ActivityLog::query()
            ->with('user:id,name')
            ->latest('id')
            ->take(8)
            ->get();

        return compact(
            'totalUsers',
            'activeUsers',
            'onlineUsers',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'overdueTasks',
            'activities',
        );
    }

    public function statistics(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('admin.statistics', $this->statisticsData());
    }

    private function statisticsData(): array
    {
        $today = now()->startOfDay();
        $periodStart = $today->copy()->subDays(13);
        $dateRange = fn ($query) => $query->where('created_at', '>=', $periodStart);

        $userCounts = $dateRange(User::query()->where('role', 'user'))
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'activity_date');
        $taskCounts = $dateRange(Task::query())
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'activity_date');
        $completedCounts = ActivityLog::query()
            ->where('type', 'completed')
            ->where('created_at', '>=', $periodStart)
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'activity_date');

        $labels = [];
        $userTrend = [];
        $taskTrend = [];
        $completedTrend = [];

        for ($day = $periodStart->copy(); $day->lte($today); $day->addDay()) {
            $date = $day->toDateString();
            $labels[] = $day->locale('id')->isoFormat('D MMM');
            $userTrend[] = (int) ($userCounts[$date] ?? 0);
            $taskTrend[] = (int) ($taskCounts[$date] ?? 0);
            $completedTrend[] = (int) ($completedCounts[$date] ?? 0);
        }

        $priorityCounts = Task::query()
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');
        $statusCounts = [
            'completed' => Task::where('is_completed', true)->count(),
            'pending' => Task::where('is_completed', false)->count(),
        ];
        $totalTasks = array_sum($statusCounts);
        $overdueTasks = Task::query()
            ->where('is_completed', false)
            ->whereDate('due_date', '<', today())
            ->count();
        $activityQuery = ActivityLog::query()
            ->with('user:id,name')
            ->latest('id');
        $activities = $activityQuery->paginate(12);

        return compact(
            'labels',
            'userTrend',
            'taskTrend',
            'completedTrend',
            'priorityCounts',
            'statusCounts',
            'totalTasks',
            'overdueTasks',
            'activities',
        );
    }

    private function formatActivities(Collection $activities): array
    {
        return $activities->map(fn (ActivityLog $activity): array => [
            'id' => $activity->id,
            'name' => $activity->user?->name ?? 'Pengguna dihapus',
            'type' => $activity->type,
            'action' => $activity->action,
            'subject' => $activity->subject,
            'createdAt' => $activity->created_at->toIso8601String(),
            'createdAtLabel' => $activity->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm'),
        ])->all();
    }

    public function activityAnalytics(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $today = now()->startOfDay();
        $periodStart = $today->copy()->subDays(6);

        $dailyCounts = ActivityLog::query()
            ->where('created_at', '>=', $periodStart)
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'activity_date');

        $resetRequestCountsByDay = PasswordResetRequest::query()
            ->where('created_at', '>=', $periodStart)
            ->selectRaw('DATE(created_at) as request_date, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'request_date');

        $labels = [];
        $counts = [];
        $resetRequestCounts = [];

        for ($day = $periodStart->copy(); $day->lte($today); $day->addDay()) {
            $date = $day->toDateString();
            $labels[] = $day->locale('id')->isoFormat('ddd');
            $counts[] = (int) ($dailyCounts[$date] ?? 0);
            $resetRequestCounts[] = (int) ($resetRequestCountsByDay[$date] ?? 0);
        }

        $totalTasks = Task::query()->count();
        $categories = Task::query()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($category): array => [
                'name' => $category->category ?: 'General',
                'count' => (int) $category->total,
                'percentage' => $totalTasks > 0
                    ? (int) round(((int) $category->total / $totalTasks) * 100)
                    : 0,
            ]);

        $priorityCounts = Task::query()
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $activities = ActivityLog::query()
            ->with('user:id,name')
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (ActivityLog $activity): array => [
                'id' => $activity->id,
                'name' => $activity->user?->name ?? 'Pengguna dihapus',
                'type' => $activity->type,
                'action' => $activity->action,
                'subject' => $activity->subject,
                'createdAt' => $activity->created_at->toIso8601String(),
            ]);

        return response()->json([
            'trend' => [
                'labels' => $labels,
                'data' => $counts,
            ],
            'resetRequests' => [
                'total' => (int) array_sum($resetRequestCounts),
                'labels' => $labels,
                'data' => $resetRequestCounts,
            ],
            'activities' => $activities,
            'categories' => $categories,
            'priorities' => [
                'total' => $totalTasks,
                'high' => (int) ($priorityCounts['High'] ?? 0),
                'medium' => (int) ($priorityCounts['Medium'] ?? 0),
                'low' => (int) ($priorityCounts['Low'] ?? 0),
            ],
        ]);
    }
}
