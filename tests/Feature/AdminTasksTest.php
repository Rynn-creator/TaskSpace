<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_accounts_default_to_user_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => 'user',
        ]);
    }

    public function test_users_only_see_their_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->tasks()->create(['title' => 'My task']);
        $otherUser->tasks()->create(['title' => 'Other task']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('My task')
            ->assertDontSee('Other task');

        $this->get(route('admin.user-presence'))->assertForbidden();
    }

    public function test_admin_can_only_see_and_manage_their_own_tasks(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $otherUser = User::factory()->create();
        $adminTask = $admin->tasks()->create([
            'title' => 'Admin task',
            'category' => 'Project',
            'priority' => 'Low',
            'due_date' => '2026-12-01',
        ]);
        $otherTask = $otherUser->tasks()->create([
            'title' => 'Other user task',
            'category' => 'Personal',
            'priority' => 'Low',
            'due_date' => '2026-12-01',
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Admin task')
            ->assertDontSee('Other user task');

        $this->actingAs($admin)
            ->patch(route('tasks.update', $adminTask), [
                'title' => 'Updated by admin',
                'category' => 'Project',
                'priority' => 'High',
                'due_date' => '2026-12-02',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $adminTask->id,
            'title' => 'Updated by admin',
        ]);

        $this->patch(route('tasks.update', $otherTask), ['toggle_status' => '1'])
            ->assertForbidden();

        $this->delete(route('tasks.destroy', $otherTask))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $otherTask->id]);
    }

    public function test_admin_dashboard_shows_user_online_and_offline_counts(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $onlineUser = User::factory()->create();
        $offlineUser = User::factory()->create();

        DB::table('user_presence')->insert([
            [
                'user_id' => $onlineUser->id,
                'last_seen_at' => now()->subMinutes(4),
            ],
            [
                'user_id' => $offlineUser->id,
                'last_seen_at' => now()->subMinutes(6),
            ],
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Total User')
            ->assertSee('Online')
            ->assertSee('Offline')
            ->assertViewHas('userCount', 2)
            ->assertViewHas('onlineUsers', 1)
            ->assertViewHas('offlineUsers', 1)
            ->assertViewHas('onlinePercentage', 50);

        $this->get(route('admin.user-presence'))
            ->assertOk()
            ->assertJson([
                'userCount' => 2,
                'onlineUsers' => 1,
                'offlineUsers' => 1,
                'onlinePercentage' => 50,
            ]);
    }

    public function test_admin_activity_analytics_returns_recent_activity_and_seven_day_trend(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create(['name' => 'Analytics User']);

        $user->tasks()->create([
            'title' => 'School task',
            'category' => 'Sekolah',
            'priority' => 'High',
        ]);
        $user->tasks()->create([
            'title' => 'Personal task',
            'category' => 'Personal',
            'priority' => 'Medium',
        ]);
        $user->tasks()->create([
            'title' => 'Another school task',
            'category' => 'Sekolah',
            'priority' => 'Low',
        ]);

        DB::table('password_reset_requests')->insert([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => 'pending',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        DB::table('activity_logs')->insert([
            [
                'user_id' => $user->id,
                'type' => 'created',
                'action' => 'membuat task baru',
                'subject' => 'Task minggu ini',
                'created_at' => now()->subDays(2)->toDateTimeString(),
                'updated_at' => now()->subDays(2)->toDateTimeString(),
            ],
            [
                'user_id' => $user->id,
                'type' => 'completed',
                'action' => 'menyelesaikan task',
                'subject' => 'Task hari ini',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.activity-analytics'))
            ->assertOk()
            ->assertJsonCount(7, 'trend.labels')
            ->assertJsonCount(7, 'trend.data')
            ->assertJsonPath('trend.data.4', 1)
            ->assertJsonPath('trend.data.6', 1)
            ->assertJsonPath('resetRequests.total', 1)
            ->assertJsonPath('resetRequests.data.6', 1)
            ->assertJsonPath('categories.0.name', 'Sekolah')
            ->assertJsonPath('categories.0.count', 2)
            ->assertJsonPath('categories.0.percentage', 67)
            ->assertJsonPath('priorities.total', 3)
            ->assertJsonPath('priorities.high', 1)
            ->assertJsonPath('priorities.medium', 1)
            ->assertJsonPath('priorities.low', 1)
            ->assertJsonPath('activities.0.name', 'Analytics User')
            ->assertJsonPath('activities.0.subject', 'Task hari ini');
    }

    public function test_non_admin_cannot_access_activity_analytics(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('admin.activity-analytics'))
            ->assertForbidden();
    }

    public function test_admin_navigation_is_shared_across_admin_pages(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        foreach (['/', '/admin/users', '/admin/password-reset-requests', '/admin/feedback'] as $path) {
            $this->actingAs($admin)
                ->get($path)
                ->assertOk()
                ->assertSee('Statistik & Log')
                ->assertSee('Feedback Masuk')
                ->assertSee('Request Password')
                ->assertSee('Pengguna');
        }
    }

    public function test_admin_dashboard_and_analytics_show_their_own_sections(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Total User')
            ->assertSee('Request Reset Password')
            ->assertSee('Feedback Menunggu Balasan')
            ->assertDontSee('Tren Aktivitas')
            ->assertDontSee('Kategori Task')
            ->assertDontSee('Level Prioritas')
            ->assertSee('Tema:');

        $this->get(route('tasks.index', ['section' => 'analytics']))
            ->assertOk()
            ->assertSee('Tren Aktivitas')
            ->assertSee('Log Aktivitas Terbaru')
            ->assertSee('Kategori Task')
            ->assertSee('task-category-stats')
            ->assertSee('Level Prioritas')
            ->assertSee('Prioritas tinggi')
            ->assertSee('reset-request-chart')
            ->assertDontSee('Total User')
            ->assertDontSee('Request Reset Password')
            ->assertDontSee('Feedback Menunggu Balasan')
            ->assertSee('Tema:');
    }

    public function test_task_changes_are_recorded_in_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'title' => 'Tracked task',
                'category' => 'Work',
                'priority' => 'Medium',
                'due_date' => '2026-12-01',
            ])
            ->assertRedirect();

        $task = $user->tasks()->firstOrFail();
        $this->patch(route('tasks.update', $task), ['toggle_status' => '1'])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'type' => 'created',
            'subject' => 'Tracked task',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'type' => 'completed',
            'subject' => 'Tracked task',
        ]);
    }

    public function test_users_cannot_change_or_delete_other_users_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = $otherUser->tasks()->create([
            'title' => 'Private task',
            'category' => 'Personal',
            'priority' => 'Low',
            'due_date' => '2026-12-01',
        ]);

        $this->actingAs($user)
            ->patch(route('tasks.update', $task), ['toggle_status' => '1'])
            ->assertForbidden();

        $this->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
