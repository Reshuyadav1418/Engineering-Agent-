<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VcsAiReport extends Model
{
    protected $table = 'vcs_ai_reports';

    protected $fillable = [
        'employee_id',
        'summary',
        'code_quality_score',
        'collaboration_score',
        'delivery_score',
        'risk_analysis',
        'recommendations',
    ];

    protected $casts = [
        'code_quality_score' => 'integer',
        'collaboration_score' => 'integer',
        'delivery_score' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
