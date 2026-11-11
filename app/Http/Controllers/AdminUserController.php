<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $onlineSince = now()->subMinutes(5);
        $latestPresence = DB::table('user_presence')
            ->select('user_id', DB::raw('MAX(last_seen_at) as last_seen_at'))
            ->groupBy('user_id');

        $usersQuery = User::query()
            ->where('users.id', '!=', $request->user()->id)
            ->leftJoinSub($latestPresence, 'latest_presence', function ($join) {
                $join->on('users.id', '=', 'latest_presence.user_id');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.profile_photo_path',
                'users.role',
                'users.is_suspended',
                'users.created_at',
                'latest_presence.last_seen_at'
            );

        if ($search = trim((string) $request->query('search', ''))) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%');
            });
        }

        $users = $usersQuery->orderBy('users.name')->paginate(15)->withQueryString();

        $userCount = User::where('role', 'user')->count();
        $onlineCount = User::where('role', 'user')
            ->whereIn('id', function ($query) use ($onlineSince) {
                $query->select('user_id')
                    ->from('user_presence')
                    ->where('last_seen_at', '>=', $onlineSince);
            })
            ->where('is_suspended', false)
            ->count();

        return view('admin.users.index', compact('users', 'userCount', 'onlineCount', 'onlineSince'));
    }

    public function show(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $onlineSince = now()->subMinutes(5);
        $lastSeenAt = DB::table('user_presence')
            ->where('user_id', $user->id)
            ->value('last_seen_at');
        $tasks = $user->tasks();
        $totalTasks = (clone $tasks)->count();
        $completedTasks = (clone $tasks)->where('is_completed', true)->count();
        $pendingTasks = (clone $tasks)->where('is_completed', false)->count();
        $overdueTasks = (clone $tasks)
            ->where('is_completed', false)
            ->whereDate('due_date', '<', today())
            ->count();
        $priorityCounts = $user->tasks()
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');
        $isOnline = ! $user->is_suspended
            && $lastSeenAt !== null
            && \Illuminate\Support\Carbon::parse($lastSeenAt)->greaterThanOrEqualTo($onlineSince);

        return view('admin.users.show', compact(
            'user',
            'lastSeenAt',
            'isOnline',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'overdueTasks',
            'priorityCounts',
        ));
    }

    public function sendResetLink(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 403, 'Tautan reset untuk akun Anda sendiri tidak dapat dikirim dari halaman ini.');
        abort_unless($user->role === 'user', 404);

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return back()->withErrors([
                'email' => 'Pengiriman email belum dikonfigurasi. Atur MAIL_MAILER dan kredensial SMTP di .env terlebih dahulu.',
            ]);
        }

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Password reset email delivery failed.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors([
                'email' => 'Email reset gagal dikirim. Periksa konfigurasi SMTP, lalu coba lagi.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => __($status)]);
        }

        return back()->with('success', 'Link reset password telah dikirim ke email pengguna.');
    }

    public function updateRole(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 403, 'Role akun Anda tidak dapat diubah dari halaman ini.');

        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        if ($user->role === 'admin' && ! $user->is_suspended && $validated['role'] === 'user') {
            $this->ensureAnotherActiveAdminExists();
        }

        $user->forceFill(['role' => $validated['role']])->save();

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function toggleSuspension(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 403, 'Akun Anda tidak dapat dinonaktifkan dari halaman ini.');

        if ($user->role === 'admin' && ! $user->is_suspended) {
            $this->ensureAnotherActiveAdminExists();
        }

        $user->forceFill(['is_suspended' => ! $user->is_suspended])->save();

        if ($user->is_suspended) {
            DB::table('user_presence')->where('user_id', $user->id)->delete();
        }

        return back()->with(
            'success',
            $user->is_suspended ? 'Akun berhasil dinonaktifkan.' : 'Akun berhasil diaktifkan kembali.'
        );
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 403, 'Akun Anda tidak dapat dihapus dari halaman ini.');

        if ($user->role === 'admin' && ! $user->is_suspended) {
            $this->ensureAnotherActiveAdminExists();
        }

        $attachmentPaths = $user->tasks()
            ->with('attachments')
            ->get()
            ->flatMap(fn ($task) => $task->attachments->pluck('path'))
            ->all();

        if ($attachmentPaths !== [] && ! Storage::disk('local')->delete($attachmentPaths)) {
            throw new RuntimeException('Lampiran pengguna tidak dapat dihapus dari penyimpanan.');
        }

        $user->notifications()->delete();
        $user->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    private function ensureAnotherActiveAdminExists(): void
    {
        $activeAdminCount = User::query()
            ->where('role', 'admin')
            ->where('is_suspended', false)
            ->count();

        abort_if($activeAdminCount <= 1, 403, 'Aksi ini akan menghilangkan satu-satunya admin aktif.');
    }

}
