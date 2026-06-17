<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VcsMetric extends Model
{
    protected $table = 'vcs_metrics';

    protected $fillable = [
        'employee_id',
        'provider',
        'git_username',
        'commits',
        'pull_requests',
        'reviews',
        'bugs_fixed',
        'deployments',
        'deployment_frequency',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'commits' => 'integer',
        'pull_requests' => 'integer',
        'reviews' => 'integer',
        'bugs_fixed' => 'integer',
        'deployments' => 'integer',
        'deployment_frequency' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
