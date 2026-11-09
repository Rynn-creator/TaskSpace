<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Task;
use App\Models\Feedback;
use App\Models\PasswordResetRequest;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $search = $request->query('search');
        $user = $request->user();

        $query = $user->tasks()->latest();

        if ($filter === 'pending') {
            $query->where('is_completed', false);
        } elseif ($filter === 'completed') {
            $query->where('is_completed', true);
        }

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $tasks = $query->get();

        $statsQuery = $user->tasks();
        $totalTasks = (clone $statsQuery)->count();
        $pendingTasks = (clone $statsQuery)->where('is_completed', false)->count();
        $completedTasks = (clone $statsQuery)->where('is_completed', true)->count();

        // Hitung Persentase Progress Bar
        $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $presence = $user->isAdmin()
            ? $this->userPresenceStats()
            : ['userCount' => 0, 'onlineUsers' => 0, 'offlineUsers' => 0, 'onlinePercentage' => 0];
        $pendingFeedbacks = $user->isAdmin()
            ? Feedback::with('user')->whereNull('admin_reply')->latest()->take(5)->get()
            : collect();
        $pendingFeedbackCount = $user->isAdmin()
            ? Feedback::whereNull('admin_reply')->count()
            : 0;
        $pendingPasswordResetRequests = $user->isAdmin()
            ? PasswordResetRequest::with('user')->where('status', 'pending')->latest()->take(5)->get()
            : collect();
        $pendingPasswordResetCount = $user->isAdmin()
            ? PasswordResetRequest::where('status', 'pending')->count()
            : 0;

        return view('tasks.index', compact(
            'tasks', 'totalTasks', 'pendingTasks', 'completedTasks', 'progressPercentage',
            'pendingFeedbacks', 'pendingFeedbackCount', 'pendingPasswordResetRequests',
            'pendingPasswordResetCount', 'filter', 'search'
        ) + $presence);
    }

    public function userPresence(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json($this->userPresenceStats());
    }

    private function userPresenceStats(): array
    {
        $userCount = User::where('role', 'user')->count();
        $onlineSince = now()->subMinutes(5);

        $onlineUsers = DB::table('user_presence')
            ->join('users', 'users.id', '=', 'user_presence.user_id')
            ->where('users.role', 'user')
            ->where('user_presence.last_seen_at', '>=', $onlineSince)
            ->distinct()
            ->count('user_presence.user_id');

        return [
            'userCount' => $userCount,
            'onlineUsers' => $onlineUsers,
            'offlineUsers' => $userCount - $onlineUsers,
            'onlinePercentage' => $userCount > 0 ? round(($onlineUsers / $userCount) * 100) : 0,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'required|date',
        ]);

        $task = Auth::user()->tasks()->create([
            'title' => $request->title,
            'category' => $request->category,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);
        $this->logActivity($request, 'created', 'membuat task baru', $task->title);

        return back()->with('success', 'Task berhasil ditambahkan!');
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        if ($request->has('toggle_status')) {
            $task->update([
                'is_completed' => !$task->is_completed,
            ]);

            $this->logActivity(
                $request,
                $task->is_completed ? 'completed' : 'reopened',
                $task->is_completed ? 'menyelesaikan task' : 'membuka kembali task',
                $task->title
            );

            $pesan = $task->is_completed ? 'Task berhasil diselesaikan! 🎉' : 'Task dikembalikan ke pending!';
            return back()->with('success', $pesan);
        }

        $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'required|date',
        ]);

        $task->update([
            'title' => $request->title,
            'category' => $request->category,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);
        $this->logActivity($request, 'updated', 'memperbarui task', $task->title);

        return back()->with('success', 'Task berhasil diperbarui!');
    }

    public function destroy(Request $request, Task $task)
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $this->logActivity($request, 'deleted', 'menghapus task', $task->title);
        $task->delete();
        return back()->with('success', 'Task berhasil dihapus!');
    }

    private function logActivity(Request $request, string $type, string $action, string $subject): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'action' => $action,
            'subject' => $subject,
        ]);
    }
}