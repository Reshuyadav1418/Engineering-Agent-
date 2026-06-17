<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIReport extends Model
{
    protected $table = 'ai_reports';
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'summary',
        'strengths',
        'weaknesses',
        'suggestions',
        'created_at',
    ];

    protected $casts = [
        'strengths' => 'array',
        'weaknesses' => 'array',
        'suggestions' => 'array',
        'created_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
