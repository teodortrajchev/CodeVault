<?php

namespace App\Notifications;

use App\Models\ProjectInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ProjectInvitation $invitation,
        public string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->invitation->project;

        return (new MailMessage)
            ->subject("You've been invited to {$project->name}")
            ->greeting('Hello!')
            ->line("{$this->invitation->inviter->name} invited you to collaborate on {$project->name} as " . ucfirst($this->invitation->role) . '.')
            ->action('Review invitation', route('invitations.show', $this->token))
            ->line('This invitation expires on ' . $this->invitation->expires_at->format('M j, Y') . '.');
    }
}