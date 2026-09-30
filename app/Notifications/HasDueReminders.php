<?php

namespace App\Notifications;
/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @method static void saving(\Closure|string $callback)
 */
trait HasDueReminders
{
    public function initializeHasDueReminders(): void
    {
        $this->mergeCasts([
            'due_soon_notified_at' => 'datetime',
            'due_today_notified_at' => 'datetime',
        ]);
    }

    protected static function bootHasDueReminders(): void
    {
        // If the deadline (or whoever is reminded) changes, the reminders should fire again.
        static::saving(function ($model) {
            if ($model->exists && $model->isDirty($model->reminderTriggers())) {
                $model->due_soon_notified_at = null;
                $model->due_today_notified_at = null;
            }
        });
    }

    /** Attributes that, when changed, reset the "already reminded" flags. */
    protected function reminderTriggers(): array
    {
        return ['due_date'];
    }
}