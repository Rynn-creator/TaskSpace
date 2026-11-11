<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly string $reminderType,
        private readonly string $reminderKey,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'deadline' => $this->task->due_date?->toDateString(),
            'reminder_type' => $this->reminderType,
            'reminder_key' => $this->reminderKey,
        ];
    }
}
