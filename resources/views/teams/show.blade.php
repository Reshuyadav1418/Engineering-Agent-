@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:1200px;">

    {{-- Top Navigation & Action --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('teams.index') }}" class="btn-back">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5m7 7l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="page-title">{{ $team->name }}</h1>
                <p class="page-subtitle">{{ $team->description ?? 'No description.' }}</p>
            </div>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('teams.edit', $team) }}" class="btn-warn" style="padding: 9px 18px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:4px; display:inline-block; vertical-align:middle;"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Team
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:28px;">
        {{-- Productivity --}}
        <div class="stat-card">
            <div class="stat-label">Team Productivity</div>
            <div class="stat-value" style="color:#10b981;">{{ number_format($team->productivity_score, 1) }}</div>
            <div style="margin-top:10px;" class="progress-track">
                <div class="progress-bar" style="width:{{ min(100, $team->productivity_score * 10) }}%; background:#10b981;"></div>
            </div>
            <div class="stat-sub">Average of team member scores</div>
        </div>

        {{-- Team Score --}}
        <div class="stat-card">
            <div class="stat-label">Team Score</div>
            <div class="stat-value" style="color:#8b5cf6;">{{ number_format($team->team_score, 1) }}</div>
            <div style="margin-top:10px;" class="progress-track">
                <div class="progress-bar" style="width:{{ min(100, $team->team_score * 10) }}%; background:#8b5cf6;"></div>
            </div>
            <div class="stat-sub">Average of team leadership scores</div>
        </div>

        {{-- Active Tasks --}}
        <div class="stat-card">
            <div class="stat-label">Active Tasks</div>
            <div class="stat-value" style="color:#fbbf24;">{{ $team->active_tasks->count() }}</div>
            <div class="stat-sub">Pending or In Progress tasks</div>
        </div>

        {{-- Completed Tasks --}}
        <div class="stat-card">
            <div class="stat-label">Completed Tasks</div>
            <div class="stat-value" style="color:#34d399;">{{ $team->completed_tasks->count() }}</div>
            <div class="stat-sub">Successfully closed tasks</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr; gap:28px; margin-bottom:28px;">
        {{-- Row 1: Members list --}}
        <div class="form-section" style="padding: 24px;">
            <h3 class="page-title" style="font-size:16px; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Team Members Directory ({{ $team->members->count() }})
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="padding-left:20px;">Name</th>
                            <th>Squad Role</th>
                            <th>Productivity Score</th>
                            <th>Leadership Score</th>
                            <th>Department</th>
                            <th style="text-align:right; padding-right:20px;">Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($team->members as $member)
                            <tr>
                                <td style="padding-left:20px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="avatar avatar-sm">
                                            {{ strtoupper(substr($member->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p style="font-size:13.5px; font-weight:700; color:var(--text-primary);">{{ $member->name }}</p>
                                            @if($team->team_lead_id == $member->id)
                                                <span style="font-size:9.5px; background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.25); border-radius:4px; padding:1px 4px; font-weight:700;">TEAM LEAD</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="dept-tag" style="background:rgba(139,92,246,0.08); color:#a5b4fc;">{{ $member->pivot->role ?? 'Member' }}</span></td>
                                <td>
                                    <span style="font-weight:700; color:#10b981;">
                                        {{ number_format(optional($member->productivityScores()->latest('id')->first())->productivity_score ?? 0, 1) }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight:700; color:#8b5cf6;">
                                        {{ number_format(optional($member->leadershipScores()->latest('id')->first())->leadership_score ?? 0, 1) }}
                                    </span>
                                </td>
                                <td style="color:var(--text-muted); font-size:12.5px;">{{ $member->department }}</td>
                                <td style="text-align:right; padding-right:20px;">
                                    <a href="{{ route('employees.show', $member) }}" class="btn-ghost" style="padding:4px 8px; font-size:11.5px;">View Profile</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No members are currently in this team.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Row 2: Team Tasks list --}}
        <div class="form-section" style="padding: 24px;">
            <h3 class="page-title" style="font-size:16px; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/></svg>
                Team Tasks ({{ $team->tasks->count() }})
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="padding-left:20px;">Task Title</th>
                            <th>Status</th>
                            <th>Assigned Members & Roles</th>
                            <th>Estim. Hours</th>
                            <th>Actual Hours</th>
                            <th>Assigned Date</th>
                            <th style="text-align:right; padding-right:20px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($team->tasks as $task)
                            <tr>
                                <td style="padding-left:20px;">
                                    <span style="font-size:13.5px; font-weight:700; color:var(--text-primary);">{{ $task->title }}</span>
                                </td>
                                <td>
                                    @if($task->status === 'Completed')
                                        <span class="badge badge-completed"><span class="badge-dot"></span>Completed</span>
                                    @elseif($task->status === 'In Progress')
                                        <span class="badge badge-in-progress"><span class="badge-dot"></span>In Progress</span>
                                    @else
                                        <span class="badge badge-pending"><span class="badge-dot"></span>Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                        @forelse($task->members as $tm)
                                            @if($tm->employee)
                                                <div style="display:flex; align-items:center; gap:6px; font-size:12px;">
                                                    <span style="font-weight:600; color:var(--text-secondary);">{{ $tm->employee->name }}</span>
                                                    <span style="color:var(--text-muted);">({{ $tm->role }})</span>
                                                    @if($tm->status === 'Completed')
                                                        <span style="color:#10b981; font-weight:700;">✓</span>
                                                    @endif
                                                </div>
                                            @endif
                                        @empty
                                            <span style="color:var(--text-muted); font-size:12px; font-style:italic;">No assignees configured</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td style="font-family:monospace; font-size:12.5px;">{{ number_format($task->estimated_hours, 1) }}h</td>
                                <td style="font-family:monospace; font-size:12.5px;">{{ number_format($task->actual_hours, 1) }}h</td>
                                <td style="color:var(--text-muted); font-size:12.5px;">{{ $task->assigned_date ? $task->assigned_date->format('Y-m-d') : 'N/A' }}</td>
                                <td style="text-align:right; padding-right:20px;">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn-ghost" style="padding:4px 8px; font-size:11.5px;">View Task</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No tasks are currently assigned to this team.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
