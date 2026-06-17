@extends('layouts.app')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('tasks.index') }}" class="p-2 rounded-xl bg-slate-900/60 border border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-black">Task Details</h1>
                <p class="text-black text-xs mt-1">Review description, timelines, workload estimates, and collaborative assignees.</p>
            </div>
        </div>
        <div>
            <a href="{{ route('tasks.edit', $task->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-indigo-600/15 text-indigo-400 border border-indigo-500/20 font-semibold text-xs hover:bg-indigo-600/20 transition-all duration-200">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Task
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="glass border border-slate-800 rounded-2xl p-8 shadow-xl space-y-6">
        
        <!-- Title & Status -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800/60">
            <h2 class="text-2xl font-bold text-black tracking-tight leading-tight">{{ $task->title }}</h2>
            <div>
                @if ($task->status === 'Completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2"></span>
                        Completed
                    </span>
                @elseif ($task->status === 'In Progress')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-2 animate-pulse"></span>
                        In Progress
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-500/15 text-slate-400 border border-slate-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-2"></span>
                        Pending
                    </span>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-2.5">
            <h3 class="text-xs font-semibold text-black uppercase tracking-wider">Description</h3>
            <p class="text-black leading-relaxed text-sm whitespace-pre-line bg-slate-900/35 border border-slate-800/30 rounded-xl p-5">
                {{ $task->description ?? 'No description provided for this task.' }}
            </p>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 pt-4">
            
            <!-- Assignee / Team -->
            <div class="space-y-1 bg-slate-900/20 border border-slate-800/40 rounded-xl p-4">
                <span class="block text-[10px] font-semibold text-black uppercase tracking-wider">Assignment</span>
                @if ($task->isIndividual() && $task->employee)
                    <a href="{{ route('employees.show', $task->employee->id) }}" class="font-bold text-indigo-400 hover:underline text-sm block truncate">
                        {{ $task->employee->name }}
                    </a>
                    <span class="text-[10px] text-black block mt-0.5">Individual Assignment</span>
                @elseif ($task->isTeam() && $task->team)
                    <a href="{{ route('teams.show', $task->team->id) }}" class="font-bold text-violet-400 hover:underline text-sm block truncate">
                        {{ $task->team->name }}
                    </a>
                    <span class="text-[10px] text-black block mt-0.5">Team Assignment</span>
                @else
                    <span class="font-semibold text-slate-600 italic text-xs block">Unassigned</span>
                @endif
            </div>

            <!-- Estimated Time -->
            <div class="space-y-1 bg-slate-900/20 border border-slate-800/40 rounded-xl p-4">
                <span class="block text-[10px] font-semibold text-black uppercase tracking-wider">Total Estimated</span>
                <span class="font-mono font-bold text-black text-sm block mt-0.5">
                    {{ number_format($task->estimated_hours, 1) }} hours
                </span>
            </div>

            <!-- Actual Hours -->
            <div class="space-y-1 bg-slate-900/20 border border-slate-800/40 rounded-xl p-4">
                <span class="block text-[10px] font-semibold text-black uppercase tracking-wider">Total Actual Spent</span>
                <span class="font-mono font-bold text-black text-sm block mt-0.5">
                    {{ number_format($task->actual_hours ?? 0, 1) }} hours
                </span>
            </div>

            <!-- Task ID -->
            <div class="space-y-1 bg-slate-900/20 border border-slate-800/40 rounded-xl p-4">
                <span class="block text-[10px] font-semibold text-black uppercase tracking-wider">Task ID</span>
                <span class="font-mono font-bold text-black text-sm block mt-0.5">
                    #{{ $task->id }}
                </span>
            </div>

        </div>

        <!-- Assigned Members Collaboration (If Team Assignment) -->
        @if ($task->isTeam())
            <div class="space-y-4 pt-6 border-t border-slate-800/60">
                <h3 class="text-xs font-semibold text-black uppercase tracking-wider">Assigned Members Collaboration</h3>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="padding-left:16px;">Employee</th>
                                <th>Role</th>
                                <th>Assigned Hours</th>
                                <th>Actual Hours</th>
                                <th>Status</th>
                                <th>Timestamps</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($task->members as $member)
                                <tr>
                                    <td style="padding-left:16px;">
                                        @if($member->employee)
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <div class="avatar avatar-sm" style="width:24px; height:24px; font-size:9.5px;">
                                                    {{ strtoupper(substr($member->employee->name, 0, 2)) }}
                                                </div>
                                                <a href="{{ route('employees.show', $member->employee->id) }}" class="font-semibold text-indigo-400 hover:underline">
                                                    {{ $member->employee->name }}
                                                </a>
                                            </div>
                                        @else
                                            <span style="color:var(--text-muted);">Removed Employee</span>
                                        @endif
                                    </td>
                                    <td><span class="dept-tag">{{ $member->role }}</span></td>
                                    <td class="font-mono text-xs text-black">{{ number_format($member->assigned_hours, 1) }}h</td>
                                    <td class="font-mono text-xs text-black">{{ number_format($member->actual_hours, 1) }}h</td>
                                    <td>
                                        @if ($member->status === 'Completed')
                                            <span class="badge badge-completed"><span class="badge-dot"></span>Completed</span>
                                        @elseif ($member->status === 'In Progress')
                                            <span class="badge badge-in-progress"><span class="badge-dot"></span>In Progress</span>
                                        @else
                                            <span class="badge badge-pending"><span class="badge-dot"></span>Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-xs text-black">
                                        @if($member->started_at)
                                            <div>Start: {{ $member->started_at->format('Y-m-d') }}</div>
                                        @endif
                                        @if($member->completed_at)
                                            <div>End: {{ $member->completed_at->format('Y-m-d') }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:30px; color:var(--text-muted);">No members are assigned to this team task yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Timelines Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-800/60 text-xs">
            <div class="flex items-center space-x-2 text-black">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Assigned on <strong class="text-black font-semibold">{{ \Carbon\Carbon::parse($task->assigned_date)->format('F d, Y') }}</strong></span>
            </div>
            
            @if ($task->completed_date)
                <div class="flex items-center space-x-2 text-black sm:justify-end">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Completed on <strong class="text-black font-semibold">{{ \Carbon\Carbon::parse($task->completed_date)->format('F d, Y') }}</strong></span>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
