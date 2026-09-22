<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'description', 'status', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}