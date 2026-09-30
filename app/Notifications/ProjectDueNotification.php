<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectDueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public int $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $when = $this->daysLeft === 0 ? 'today' : 'tomorrow';

        return (new MailMessage)
            ->subject("Project due {$when}: {$this->project->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("The project \"{$this->project->name}\" is due {$when} ({$this->project->due_date->format('M j, Y')}).")
            ->action('Open project', route('projects.show', $this->project));
    }
}