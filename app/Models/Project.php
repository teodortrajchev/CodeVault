<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ProjectRole;

class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'description', 'status', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function roleFor(User $user): ?string
    {
        if ($this->owner_id === $user->id) {
            return ProjectRole::Owner->value;
        }

        return $this->members()->where('user_id', $user->id)->first()?->pivot->role;
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
    
}