<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadershipScore extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'productivity_score',
        'leadership_score',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
