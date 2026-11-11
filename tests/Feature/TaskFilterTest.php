<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_filters_show_matching_tasks_and_one_active_sidebar_item_with_user_wide_statistics(): void
    {
        $user = User::factory()->create();
        $user->tasks()->create(['title' => 'Pending task one']);
        $user->tasks()->create(['title' => 'Pending task two']);
        $user->tasks()->create(['title' => 'Completed task', 'is_completed' => true]);
        User::factory()->create()->tasks()->create(['title' => 'Another user task']);

        $cases = [
            [[], ['Pending task one', 'Pending task two', 'Completed task'], 'Semua Tugas'],
            [['filter' => 'pending'], ['Pending task one', 'Pending task two'], 'Belum Selesai'],
            [['filter' => 'completed'], ['Completed task'], 'Selesai'],
        ];

        $this->actingAs($user);

        foreach ($cases as [$query, $expectedTitles, $activeLabel]) {
            $response = $this->get(route('tasks.index', $query))
                ->assertOk()
                ->assertViewHas('tasks', fn ($tasks) => $tasks->pluck('title')->all() === $expectedTitles)
                ->assertViewHas('totalTasks', 3)
                ->assertViewHas('pendingTasks', 2)
                ->assertViewHas('completedTasks', 1)
                ->assertViewHas('progressPercentage', 33)
                ->assertSee('33%');

            preg_match_all(
                '/<a\b(?=[^>]*aria-current="page")[^>]*>(.*?)<\/a>/s',
                $response->getContent(),
                $activeLinks
            );

            $this->assertCount(1, $activeLinks[1]);
            $this->assertStringContainsString($activeLabel, strip_tags($activeLinks[1][0]));
        }
    }

    public function test_task_mutations_are_reflected_in_statistics_after_the_page_refreshes(): void
    {
        $user = User::factory()->create();
        $pendingTask = $user->tasks()->create(['title' => 'Pending task']);
        $completedTask = $user->tasks()->create([
            'title' => 'Completed task',
            'is_completed' => true,
        ]);

        $this->actingAs($user);

        $this->post(route('tasks.store'), [
            'title' => 'New task',
            'category' => 'Work',
            'priority' => 'Medium',
            'due_date' => '2026-12-01',
        ])->assertRedirect();

        $this->get(route('tasks.index', ['filter' => 'pending']))
            ->assertViewHas('totalTasks', 3)
            ->assertViewHas('pendingTasks', 2)
            ->assertViewHas('completedTasks', 1)
            ->assertViewHas('progressPercentage', 33)
            ->assertSee('New task');

        $this->patch(route('tasks.update', $pendingTask), ['toggle_status' => '1'])
            ->assertRedirect();

        $this->get(route('tasks.index', ['filter' => 'pending']))
            ->assertViewHas('totalTasks', 3)
            ->assertViewHas('pendingTasks', 1)
            ->assertViewHas('completedTasks', 2)
            ->assertViewHas('progressPercentage', 67)
            ->assertDontSee('Pending task');

        $this->delete(route('tasks.destroy', $completedTask))->assertRedirect();

        $this->get(route('tasks.index'))
            ->assertViewHas('totalTasks', 2)
            ->assertViewHas('pendingTasks', 1)
            ->assertViewHas('completedTasks', 1)
            ->assertViewHas('progressPercentage', 50);
    }
}
