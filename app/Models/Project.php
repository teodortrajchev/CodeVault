<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\ProjectRole;

class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'description', 'status', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function roleFor(?User $user): ?ProjectRole
    {
        if (! $user) {
            return null;
        }

        $member = $this->members->firstWhere('id', $user->id);

        return $member ? ProjectRole::from($member->pivot->role) : null;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
    
}