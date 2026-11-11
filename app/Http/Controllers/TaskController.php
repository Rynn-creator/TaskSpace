<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Feedback;
use App\Models\PasswordResetRequest;
use App\Notifications\DeadlineReminder;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Throwable;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin() && ! $request->query('section')) {
            return redirect()->route('admin.dashboard');
        }

        $filter = $request->query('filter');
        $search = $request->query('search');

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
        $deadlineNotifications = $user->unreadNotifications()
            ->where('type', DeadlineReminder::class)
            ->latest()
            ->get();

        return view('tasks.index', compact(
            'tasks', 'totalTasks', 'pendingTasks', 'completedTasks', 'progressPercentage',
            'pendingFeedbacks', 'pendingFeedbackCount', 'pendingPasswordResetRequests',
            'pendingPasswordResetCount', 'deadlineNotifications', 'filter', 'search'
        ) + $presence);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->load('attachments');

        return view('tasks.show', compact('task'));
    }

    public function edit(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);
        $task->load('attachments');

        return view('tasks.edit', compact('task'));
    }

    public function downloadAttachment(Request $request, TaskAttachment $attachment)
    {
        $task = $attachment->task;
        $this->authorizeTask($request, $task);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroyAttachment(Request $request, TaskAttachment $attachment)
    {
        $this->authorizeTask($request, $attachment->task);

        $disk = Storage::disk('local');
        if ($disk->exists($attachment->path) && ! $disk->delete($attachment->path)) {
            throw new RuntimeException('Lampiran tidak dapat dihapus dari penyimpanan.');
        }

        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function markReminderRead(Request $request, string $notification)
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        abort_unless($record->type === DeadlineReminder::class, 404);
        $record->markAsRead();

        return back()->with('success', 'Pengingat ditandai sudah dibaca.');
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
        $data = $request->validate($this->taskValidationRules());
        $storedPaths = [];

        try {
            $task = DB::transaction(function () use ($request, $data, &$storedPaths): Task {
                $task = $request->user()->tasks()->create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'],
                    'priority' => $data['priority'],
                    'due_date' => $data['due_date'],
                ]);
                $this->storeAttachments($task, $request->file('attachments', []), $storedPaths);

                return $task;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        $this->logActivity($request, 'created', 'membuat task baru', $task->title);

        return redirect()->route('tasks.index')
            ->with('success', 'Task berhasil ditambahkan!')
            ->with('createdTaskUrl', route('tasks.show', $task));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);

        if ($request->has('toggle_status')) {
            $task->update([
                'is_completed' => !$task->is_completed,
            ]);
            if ($task->is_completed) {
                $this->clearTaskNotifications($task);
            } else {
                $this->deleteTaskNotifications($task);
            }

            $this->logActivity(
                $request,
                $task->is_completed ? 'completed' : 'reopened',
                $task->is_completed ? 'menyelesaikan task' : 'membuka kembali task',
                $task->title
            );

            $pesan = $task->is_completed ? 'Task berhasil diselesaikan! 🎉' : 'Task dikembalikan ke pending!';
            return back()->with('success', $pesan);
        }

        $data = $request->validate($this->taskValidationRules());
        $storedPaths = [];

        try {
            DB::transaction(function () use ($task, $request, $data, &$storedPaths): void {
                $task->update([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'],
                    'priority' => $data['priority'],
                    'due_date' => $data['due_date'],
                ]);
                $this->storeAttachments($task, $request->file('attachments', []), $storedPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        $this->clearTaskNotifications($task);
        $this->logActivity($request, 'updated', 'memperbarui task', $task->title);

        return redirect()->route('tasks.show', $task)->with('success', 'Task berhasil diperbarui!');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTask($request, $task);

        $disk = Storage::disk('local');
        $paths = $task->attachments()->pluck('path')->all();
        if ($paths !== [] && ! $disk->delete($paths)) {
            throw new RuntimeException('Lampiran task tidak dapat dihapus dari penyimpanan.');
        }

        $this->logActivity($request, 'deleted', 'menghapus task', $task->title);
        $this->deleteTaskNotifications($task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task berhasil dihapus!');
    }

    private function taskValidationRules(): array
    {
        $mimes = 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,txt,csv,zip';
        $mimeTypes = implode(',', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'text/csv',
            'application/csv',
            'application/zip',
            'application/x-zip-compressed',
        ]);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:Low,Medium,High'],
            'due_date' => ['required', 'date'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240', "mimes:{$mimes}", "extensions:{$mimes}", "mimetypes:{$mimeTypes}"],
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, string>  $storedPaths
     */
    private function storeAttachments(Task $task, array $files, array &$storedPaths): void
    {
        foreach ($files as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs(
                "task-attachments/{$task->user_id}/{$task->id}",
                Str::uuid() . '.' . $extension,
                'local'
            );

            if ($path === false) {
                throw new RuntimeException('Lampiran gagal disimpan.');
            }

            $storedPaths[] = $path;
            $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));

            $task->attachments()->create([
                'original_name' => mb_substr($originalName, 0, 255),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403);
    }

    private function clearTaskNotifications(Task $task): void
    {
        $task->user->notifications()
            ->where('type', DeadlineReminder::class)
            ->where('data->task_id', $task->id)
            ->update(['read_at' => now()]);
    }

    private function deleteTaskNotifications(Task $task): void
    {
        $task->user->notifications()
            ->where('type', DeadlineReminder::class)
            ->where('data->task_id', $task->id)
            ->delete();
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