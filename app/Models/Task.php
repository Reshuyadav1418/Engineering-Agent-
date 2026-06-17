<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'employee_id',
        'team_id',
        'title',
        'description',
        'status',
        'assigned_date',
        'completed_date',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'completed_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function members()
    {
        return $this->hasMany(TaskMember::class);
    }

    public function isIndividual(): bool
    {
        return !empty($this->employee_id);
    }

    public function isTeam(): bool
    {
        return !empty($this->team_id);
    }
}
