<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\HasDueReminders;
class Task extends Model
{
    use HasFactory, HasDueReminders;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'assigned_to',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    protected function reminderTriggers(): array
    {
        return ['due_date', 'assigned_to'];
    }
}