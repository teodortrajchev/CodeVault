<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public int $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->daysLeft === 0 ? 'today' : 'tomorrow';
        $project = $this->task->project;

        return (new MailMessage)
            ->subject("Task due {$when}: {$this->task->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your task \"{$this->task->name}\" in {$project->name} is due {$when} ({$this->task->due_date->format('M j, Y')}).")
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->action('Open project', route('projects.show', $project));
    }
}