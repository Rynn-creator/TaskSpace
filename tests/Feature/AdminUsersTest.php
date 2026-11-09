<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_directory_with_presence_but_no_task_data(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $onlineUser = User::factory()->create(['name' => 'Online Member']);
        $offlineUser = User::factory()->create(['name' => 'Offline Member']);
        $onlineUser->tasks()->create([
            'title' => 'Private task title',
            'category' => 'Personal',
            'priority' => 'Low',
            'due_date' => '2026-12-01',
        ]);

        DB::table('user_presence')->insert([
            'user_id' => $onlineUser->id,
            'last_seen_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Online Member')
            ->assertSee('Offline Member')
            ->assertSee($onlineUser->email)
            ->assertDontSee('Private task title')
            ->assertViewHas('onlineCount', 1)
            ->assertViewHas('userCount', 2);
    }

    public function test_authenticated_heartbeat_records_presence(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($user)
            ->get(route('session.heartbeat'))
            ->assertNoContent();

        $this->assertDatabaseHas('user_presence', ['user_id' => $user->id]);
        $lastSeenAt = DB::table('user_presence')->where('user_id', $user->id)->value('last_seen_at');
        $this->assertTrue(now()->diffInMinutes($lastSeenAt) <= 1);

        $this->actingAs($admin)
            ->get(route('admin.user-presence'))
            ->assertOk()
            ->assertJson([
                'userCount' => 1,
                'onlineUsers' => 1,
                'offlineUsers' => 0,
                'onlinePercentage' => 100,
            ]);
    }

    public function test_regular_user_cannot_view_admin_user_directory(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_change_role_suspend_reactivate_and_delete_another_account(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Ubah Role')
            ->assertSee('Suspend')
            ->assertSee('Hapus');

        $this->patch(route('admin.users.role', $user), ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('admin', $user->fresh()->role);

        $this->patch(route('admin.users.role', $user), ['role' => 'user'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame('user', $user->fresh()->role);

        DB::table('user_presence')->insert([
            'user_id' => $user->id,
            'last_seen_at' => now(),
        ]);
        $this->patch(route('admin.users.suspension', $user))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertTrue($user->fresh()->is_suspended);
        $this->assertDatabaseMissing('user_presence', ['user_id' => $user->id]);

        $this->actingAs($user->fresh())
            ->get(route('session.heartbeat'))
            ->assertRedirect(route('login'));
        $this->assertGuest();

        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $user))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertFalse($user->fresh()->is_suspended);

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_change_role_suspend_or_delete_their_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertForbidden();

        $this->patch(route('admin.users.suspension', $admin))
            ->assertForbidden();

        $this->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertFalse($admin->fresh()->is_suspended);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_suspended' => true])->save();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_sidebar_shows_feedback_count_and_user_link(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $user->feedbacks()->create(['message' => 'Needs reply']);

        $this->actingAs($admin)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Feedback Masuk')
            ->assertSee('Request Password')
            ->assertSee('Pengguna')
            ->assertSee('1')
            ->assertDontSee('Filter Tasks')
            ->assertDontSee('Pending');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Feedback Masuk')
            ->assertSee('Request Password')
            ->assertSee('Pengguna')
            ->assertSee('1');
    }
}
