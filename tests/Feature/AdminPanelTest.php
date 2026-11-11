<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_uses_live_user_task_and_presence_data(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $activeUser = User::factory()->create();
        $suspendedUser = User::factory()->create(['is_suspended' => true]);

        $activeUser->tasks()->create([
            'title' => 'Completed',
            'priority' => 'High',
            'is_completed' => true,
        ]);
        $activeUser->tasks()->create([
            'title' => 'Overdue',
            'priority' => 'Medium',
            'due_date' => today()->subDay(),
        ]);
        $activeUser->tasks()->create([
            'title' => 'Upcoming',
            'priority' => 'Low',
            'due_date' => today()->addDay(),
        ]);

        DB::table('user_presence')->insert([
            'user_id' => $activeUser->id,
            'last_seen_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalUsers', 2)
            ->assertViewHas('activeUsers', 1)
            ->assertViewHas('onlineUsers', 1)
            ->assertViewHas('totalTasks', 3)
            ->assertViewHas('completedTasks', 1)
            ->assertViewHas('pendingTasks', 2)
            ->assertViewHas('overdueTasks', 1);
    }

    public function test_existing_admin_task_workspace_still_contains_only_its_own_tasks(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $ownTask = $admin->tasks()->create(['title' => 'Workspace own task']);
        $otherUser = User::factory()->create();
        $otherUser->tasks()->create(['title' => 'Workspace private task']);

        $this->actingAs($admin)
            ->get(route('tasks.index', ['section' => 'tasks']))
            ->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->contains('id', $ownTask->id))
            ->assertSee('Workspace own task')
            ->assertDontSee('Workspace private task');
    }

    public function test_admin_statistics_and_user_detail_render_database_aggregates(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create(['name' => 'Panel User']);
        $user->tasks()->create(['title' => 'High task', 'priority' => 'High']);
        $user->tasks()->create(['title' => 'Done task', 'priority' => 'Medium', 'is_completed' => true]);
        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'completed',
            'action' => 'menyelesaikan task',
            'subject' => 'Done task',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.statistics'))
            ->assertOk()
            ->assertViewHas('priorityCounts', fn ($counts) => (int) $counts['High'] === 1)
            ->assertViewHas('statusCounts', ['completed' => 1, 'pending' => 1])
            ->assertSee('Aktivitas harian')
            ->assertSee('Log aktivitas terbaru');

        $this->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertViewHas('totalTasks', 2)
            ->assertViewHas('completedTasks', 1)
            ->assertViewHas('pendingTasks', 1)
            ->assertSee('Distribusi prioritas')
            ->assertSee('Kirim tautan reset');
    }

    public function test_live_admin_endpoints_return_current_database_state(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Live task',
            'category' => 'Work',
            'priority' => 'High',
            'due_date' => today()->subDay()->toDateString(),
        ])->assertRedirect();
        $task = $user->tasks()->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('admin.live.dashboard'))
            ->assertOk()
            ->assertJsonPath('stats.totalTasks', 1)
            ->assertJsonPath('stats.pendingTasks', 1)
            ->assertJsonPath('stats.overdueTasks', 1)
            ->assertJsonPath('activities.0.action', 'membuat task baru');

        $this->actingAs($user)
            ->patch(route('tasks.update', $task), ['toggle_status' => '1']);
        $this->actingAs($admin);

        $this->getJson(route('admin.live.dashboard'))
            ->assertOk()
            ->assertJsonPath('stats.completedTasks', 1)
            ->assertJsonPath('stats.pendingTasks', 0)
            ->assertJsonPath('stats.overdueTasks', 0)
            ->assertJsonPath('activities.0.action', 'menyelesaikan task');

        $this->getJson(route('admin.live.statistics'))
            ->assertOk()
            ->assertJsonPath('priorities.High', 1)
            ->assertJsonPath('status.completed', 1)
            ->assertJsonPath('status.pending', 0)
            ->assertJsonPath('totalTasks', 1)
            ->assertJsonPath('overdueTasks', 0);

        $this->actingAs($user)->delete(route('tasks.destroy', $task))->assertRedirect();
        $this->actingAs($admin)
            ->getJson(route('admin.live.dashboard'))
            ->assertOk()
            ->assertJsonPath('stats.totalTasks', 0)
            ->assertJsonPath('stats.completedTasks', 0)
            ->assertJsonPath('activities.0.action', 'menghapus task');
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'type' => 'deleted',
            'subject' => 'Live task',
        ]);
    }

    public function test_statistics_chart_renders_database_values_for_all_tooltip_series(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $user = User::factory()->create();
        $user->tasks()->createMany([
            ['title' => 'First task', 'priority' => 'High'],
            ['title' => 'Second task', 'priority' => 'Low'],
        ]);
        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'completed',
            'action' => 'menyelesaikan task',
            'subject' => 'First task',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.statistics'))
            ->assertOk()
            ->assertViewHas('userTrend', fn ($trend) => end($trend) === 1)
            ->assertViewHas('taskTrend', fn ($trend) => end($trend) === 2)
            ->assertViewHas('completedTrend', fn ($trend) => end($trend) === 1)
            ->assertSee('data-tooltip-label="Pengguna baru"', false)
            ->assertSee('data-tooltip-label="Tugas dibuat"', false)
            ->assertSee('data-tooltip-label="Tugas selesai"', false)
            ->assertSee('data-tooltip-value="1"', false)
            ->assertSee('data-tooltip-value="2"', false)
            ->assertSee('role="img"', false)
            ->assertSee('tabindex="0"', false);
    }

    public function test_registration_login_and_logout_are_logged_without_credentials(): void
    {
        $this->post(route('register'), [
            'name' => 'Activity User',
            'email' => 'activity@example.test',
            'password' => 'private-password',
            'password_confirmation' => 'private-password',
        ])->assertRedirect(route('tasks.index'));

        $user = User::where('email', 'activity@example.test')->firstOrFail();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'type' => 'registered',
            'action' => 'mendaftar ke TaskSpace',
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login'), [
            'email' => 'activity@example.test',
            'password' => 'private-password',
        ])->assertRedirect('/');
        $this->post(route('logout'))->assertRedirect(route('login'));

        $logs = ActivityLog::query()->where('user_id', $user->id)->get();
        $this->assertEqualsCanonicalizing(
            ['registered', 'logged_in', 'logged_out', 'logged_out'],
            $logs->pluck('type')->all(),
        );
        $this->assertStringNotContainsString('activity@example.test', $logs->toJson());
        $this->assertStringNotContainsString('private-password', $logs->toJson());
    }

    public function test_user_management_search_and_feedback_status_filters_work(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $matchingUser = User::factory()->create(['name' => 'Unique Panel Name', 'email' => 'unique@example.test']);
        $otherUser = User::factory()->create(['name' => 'Other Person']);
        $matchingUser->feedbacks()->create(['message' => 'Need a reply']);
        $otherUser->feedbacks()->create([
            'message' => 'Already handled',
            'admin_reply' => 'Thank you',
            'replied_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'unique@']))
            ->assertOk()
            ->assertSee('unique@example.test')
            ->assertDontSee('Other Person');

        $this->get(route('admin.feedback.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Need a reply')
            ->assertDontSee('Already handled');

        $this->get(route('admin.feedback.index', ['status' => 'replied']))
            ->assertOk()
            ->assertSee('Already handled')
            ->assertDontSee('Need a reply');
    }

    public function test_non_admin_cannot_access_new_admin_pages_and_admin_cannot_modify_self(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
        $this->getJson(route('admin.live.dashboard'))->assertForbidden();
        $this->getJson(route('admin.live.statistics'))->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)
            ->patch(route('admin.users.suspension', $admin))
            ->assertForbidden();
        $this->patch(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertForbidden();
        $this->post(route('admin.users.password-reset', $admin))
            ->assertForbidden();
    }
}
