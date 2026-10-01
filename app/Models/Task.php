<?php

namespace App\Models;

use App\Notifications\HasDueReminders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, HasDueReminders;

    protected $fillable = [
        'name',
        'description',
        'project_id',
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

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}