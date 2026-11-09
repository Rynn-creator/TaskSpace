<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $onlineSince = now()->subMinutes(5);
        $latestPresence = DB::table('user_presence')
            ->select('user_id', DB::raw('MAX(last_seen_at) as last_seen_at'))
            ->groupBy('user_id');

        $users = User::query()
            ->where('users.id', '!=', $request->user()->id)
            ->leftJoinSub($latestPresence, 'latest_presence', function ($join) {
                $join->on('users.id', '=', 'latest_presence.user_id');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.is_suspended',
                'users.created_at',
                'latest_presence.last_seen_at'
            )
            ->orderBy('users.name')
            ->paginate(15);

        $userCount = User::where('role', 'user')->count();
        $onlineCount = User::where('role', 'user')
            ->whereIn('id', function ($query) use ($onlineSince) {
                $query->select('user_id')
                    ->from('user_presence')
                    ->where('last_seen_at', '>=', $onlineSince);
            })
            ->where('is_suspended', false)
            ->count();

        return view('admin.users', compact('users', 'userCount', 'onlineCount', 'onlineSince'));
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
