<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\DeadlineReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskManagementFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page_and_authenticated_user_sees_task_dashboard(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Atur Prioritas.')
            ->assertSee('Selesaikan Lebih Banyak.')
            ->assertSee('Task Management')
            ->assertSee('Priority Management')
            ->assertSee('Deadline Tracking')
            ->assertSee('Smart Reminder')
            ->assertSee('File Attachment')
            ->assertSee('Progress Tracking')
            ->assertSee('Mulai Atur Pekerjaanmu Hari Ini.');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Tugas Saya')
            ->assertDontSee('Atur Prioritas.');
    }

    public function test_user_can_create_task_with_multiple_private_attachments_and_view_details(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('tasks.store'), [
            'title' => 'Prepare project',
            'description' => 'Collect the supporting notes.',
            'category' => 'Project',
            'priority' => 'High',
            'due_date' => today()->toDateString(),
            'attachments' => [
                UploadedFile::fake()->createWithContent('notes.txt', 'Project notes'),
                UploadedFile::fake()->createWithContent('readme.csv', "name,status\nTask,pending"),
            ],
        ]);

        $task = $user->tasks()->with('attachments')->firstOrFail();
        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('createdTaskUrl', route('tasks.show', $task));

        $this->assertSame('Collect the supporting notes.', $task->description);
        $this->assertCount(2, $task->attachments);
        foreach ($task->attachments as $attachment) {
            $this->assertContains($attachment->mime_type, ['text/plain', 'text/csv']);
            $this->assertStringStartsWith("task-attachments/{$user->id}/{$task->id}/", $attachment->path);
            Storage::disk('local')->assertExists($attachment->path);
        }

        $this->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('Prepare project')
            ->assertSee('Collect the supporting notes.')
            ->assertSee('Pengingat Deadline!')
            ->assertSee('notes.txt')
            ->assertSee('Tandai Selesai');
    }

    public function test_attachments_are_private_authorized_downloadable_and_deletable(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = $owner->tasks()->create([
            'title' => 'Private task',
            'category' => 'Work',
            'priority' => 'Medium',
            'due_date' => today()->addDay()->toDateString(),
        ]);
        $path = UploadedFile::fake()->createWithContent('private.txt', 'Private attachment')
            ->storeAs("task-attachments/{$owner->id}/{$task->id}", 'unique-private.txt', 'local');
        $attachment = $task->attachments()->create([
            'original_name' => 'private.txt',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 19,
        ]);

        $this->actingAs($otherUser)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
        $this->get(route('tasks.attachments.download', $attachment))->assertForbidden();
        $this->delete(route('tasks.attachments.destroy', $attachment))->assertForbidden();
        Storage::disk('local')->assertExists($path);

        $download = $this->actingAs($owner)
            ->get(route('tasks.attachments.download', $attachment))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('attachment', $download->headers->get('Content-Disposition'));

        $this->delete(route('tasks.attachments.destroy', $attachment))->assertRedirect();
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
    }

    public function test_executable_attachments_are_rejected_and_task_edits_preserve_descriptions(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $task = $user->tasks()->create([
            'title' => 'Editable task',
            'description' => 'Original description',
            'category' => 'Work',
            'priority' => 'Low',
            'due_date' => today()->addDay()->toDateString(),
        ]);
        $this->actingAs($user);

        $this->post(route('tasks.store'), [
            'title' => 'Unsafe upload',
            'category' => 'Work',
            'priority' => 'Low',
            'due_date' => today()->addDay()->toDateString(),
            'attachments' => [UploadedFile::fake()->create('payload.exe', 1, 'application/x-msdownload')],
        ])->assertSessionHasErrors('attachments.0');

        $this->post(route('tasks.store'), [
            'title' => 'Oversized upload',
            'category' => 'Work',
            'priority' => 'Low',
            'due_date' => today()->addDay()->toDateString(),
            'attachments' => [UploadedFile::fake()->create('large.txt', 10241, 'text/plain')],
        ])->assertSessionHasErrors('attachments.0');

        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('Original description');

        $this->patch(route('tasks.update', $task), [
            'title' => 'Edited task',
            'description' => 'Updated description',
            'category' => 'Project',
            'priority' => 'High',
            'due_date' => today()->addDays(2)->toDateString(),
        ])->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Edited task',
            'description' => 'Updated description',
        ]);
    }

    public function test_deadline_command_notifies_today_and_overdue_tasks_once_and_skips_future_or_completed_tasks(): void
    {
        $user = User::factory()->create();
        $todayTask = $user->tasks()->create([
            'title' => 'Due today',
            'category' => 'Work',
            'priority' => 'High',
            'due_date' => today()->toDateString(),
        ]);
        $overdueTask = $user->tasks()->create([
            'title' => 'Already late',
            'category' => 'Work',
            'priority' => 'High',
            'due_date' => today()->subDay()->toDateString(),
        ]);
        $user->tasks()->create([
            'title' => 'Future task',
            'category' => 'Work',
            'priority' => 'Low',
            'due_date' => today()->addDay()->toDateString(),
        ]);
        $user->tasks()->create([
            'title' => 'Completed today',
            'category' => 'Work',
            'priority' => 'Low',
            'due_date' => today()->toDateString(),
            'is_completed' => true,
        ]);

        Artisan::call('tasks:send-deadline-reminders');
        Artisan::call('tasks:send-deadline-reminders');

        $notifications = $user->notifications()->where('type', DeadlineReminder::class)->get();
        $this->assertCount(2, $notifications);
        $this->assertSame(
            ['due_today', 'overdue'],
            $notifications->pluck('data.reminder_type')->sort()->values()->all()
        );
        $this->assertSame(
            [$todayTask->id, $overdueTask->id],
            $notifications->pluck('data.task_id')->sort()->values()->all()
        );

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Pengingat Deadline!')
            ->assertSee('Deadline Terlewat!')
            ->assertSee('Due today')
            ->assertSee('Already late');

        $notification = $notifications->first();
        $this->patch(route('notifications.read', $notification->id))
            ->assertRedirect();
        $this->assertNotNull($user->notifications()->findOrFail($notification->id)->read_at);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)
            ->patch(route('notifications.read', $notifications->last()->id))
            ->assertNotFound();
    }

    public function test_dashboard_lists_all_deadline_reminders_even_when_there_are_more_than_ten(): void
    {
        $user = User::factory()->create();
        for ($number = 1; $number <= 12; $number++) {
            $user->tasks()->create([
                'title' => "Deadline task {$number}",
                'category' => 'Work',
                'priority' => 'Medium',
                'due_date' => today()->toDateString(),
            ]);
        }

        Artisan::call('tasks:send-deadline-reminders');

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Deadline task 1')
            ->assertSee('Deadline task 12');

        $this->assertCount(12, $user->unreadNotifications);
    }

    public function test_completing_or_deleting_a_task_cleans_up_its_files_and_unread_reminders(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $task = $user->tasks()->create([
            'title' => 'Task to complete',
            'category' => 'Work',
            'priority' => 'Medium',
            'due_date' => today()->toDateString(),
        ]);
        $path = UploadedFile::fake()->createWithContent('task.txt', 'Task file')
            ->storeAs("task-attachments/{$user->id}/{$task->id}", 'task.txt', 'local');
        $attachment = $task->attachments()->create([
            'original_name' => 'task.txt',
            'path' => $path,
            'mime_type' => 'text/plain',
            'size' => 9,
        ]);

        Artisan::call('tasks:send-deadline-reminders');
        $this->actingAs($user)
            ->patch(route('tasks.update', $task), ['toggle_status' => '1'])
            ->assertRedirect();

        $this->assertNotNull($user->notifications()->firstOrFail()->read_at);

        $this->patch(route('tasks.update', $task), ['toggle_status' => '1'])
            ->assertRedirect();
        $this->assertCount(0, $user->notifications);

        Artisan::call('tasks:send-deadline-reminders');
        $this->assertCount(1, $user->unreadNotifications);

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
    }
}
