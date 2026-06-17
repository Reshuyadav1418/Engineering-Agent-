<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductivityScore extends Model
{
    protected $fillable = [
        'employee_id',
        'tasks_assigned',
        'tasks_completed',
        'completion_rate',
        'avg_completion_time',
        'team_contribution',
        'productivity_score',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
