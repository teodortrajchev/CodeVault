<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Task;
use App\Notifications\ProjectDueNotification;
use App\Notifications\TaskDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendDueReminders extends Command
{
    protected $signature = 'reminders:send-due';

    protected $description = 'Email reminders for tasks and projects that are due tomorrow or today';

    public function handle(): int
    {
        $stages = [
        ['column' => 'due_soon_notified_at', 'date' => today()->addDay(), 'days' => 1],
    ];

        $sent = 0;

        foreach ($stages as ['column' => $column, 'date' => $date, 'days' => $days]) {
             // Tasks: remind every assignee, skip finished tasks.
            Task::with(['project', 'assignees'])
                ->whereHas('assignees')
                ->where('status', '!=', 'completed')
                ->whereDate('due_date', $date)
                ->whereNull($column)
                ->eachById(function (Task $task) use ($column, $days, &$sent) {
                    if ($this->notify($task->assignees, new TaskDueNotification($task, $days), $task, $column)) {
                        $sent++;
                    }
                });
            // Projects: remind every member of active projects.
            Project::with('members')
                ->where('status', 'active')
                ->whereDate('due_date', $date)
                ->whereNull($column)
                ->eachById(function (Project $project) use ($column, $days, &$sent) {
                    if ($this->notify($project->members, new ProjectDueNotification($project, $days), $project, $column)) {
                        $sent++;
                    }
                });
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }

    private function notify($recipients, $notification, $model, string $column): bool
    {
        $recipients = Collection::wrap($recipients)->filter();

        if ($recipients->isEmpty()) {
            return false;
        }

        try {
            Notification::send($recipients, $notification);

            // Quietly, so the model's "reset reminders" hook doesn't fire.
            $model->forceFill([$column => now()])->saveQuietly();

            return true;
        } catch (Throwable $e) {
            // One failed email shouldn't stop the rest; it stays unmarked and is retried next run.
            Log::error('Due reminder failed', ['model' => $model::class, 'id' => $model->getKey(), 'error' => $e->getMessage()]);

            return false;
        }
    }
}