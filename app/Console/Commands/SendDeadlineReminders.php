<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\DeadlineReminder;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'tasks:send-deadline-reminders';

    protected $description = 'Create one database reminder for each task due today or overdue';

    public function handle(): int
    {
        $today = today();

        Task::query()
            ->with('user')
            ->where('is_completed', false)
            ->whereDate('due_date', '<=', $today)
            ->orderBy('id')
            ->chunkById(100, function ($tasks): void {
                foreach ($tasks as $task) {
                    if (! $task->user) {
                        continue;
                    }

                    $deadline = $task->due_date->toDateString();
                    $reminderType = $task->due_date->isToday() ? 'due_today' : 'overdue';
                    $reminderKey = "task:{$task->id}:{$reminderType}:{$deadline}";

                    $alreadySent = $task->user->notifications()
                        ->where('type', DeadlineReminder::class)
                        ->where('data->reminder_key', $reminderKey)
                        ->exists();

                    if (! $alreadySent) {
                        $task->user->notify(new DeadlineReminder($task, $reminderType, $reminderKey));
                    }
                }
            });

        $this->info('Deadline reminders processed.');

        return self::SUCCESS;
    }
}
