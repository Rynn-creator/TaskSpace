<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PasswordResetRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
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
