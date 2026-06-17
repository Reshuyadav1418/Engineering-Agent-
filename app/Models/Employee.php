<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AIReport;
use App\Models\LeadershipScore;
use App\Models\ProductivityScore;

class Employee extends Model
{
    protected $fillable = ['name',
     'email', 
     'department',
      'role', 
      'github_username'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function productivityScores(): HasMany
    {
        return $this->hasMany(ProductivityScore::class);
    }

    public function leadershipScores(): HasMany
    {
        return $this->hasMany(LeadershipScore::class);
    }

    public function aiReports(): HasMany
    {
        return $this->hasMany(AIReport::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'employee_id', 'team_id')->withPivot('role')->withTimestamps();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function taskMembers(): HasMany
    {
        return $this->hasMany(TaskMember::class);
    }

    public function collaboratingTasks()
    {
        return $this->belongsToMany(Task::class, 'task_members', 'employee_id', 'task_id')->withPivot('role', 'assigned_hours', 'actual_hours', 'status', 'started_at', 'completed_at')->withTimestamps();
    }
}
