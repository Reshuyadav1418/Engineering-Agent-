<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'description',
        'team_lead_id',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'team_lead_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'team_members', 'team_id', 'employee_id')
            ->withPivot('id', 'role')
            ->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
