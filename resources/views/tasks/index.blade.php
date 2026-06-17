@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:1200px;">

    {{-- Header --}}
    <div class="section-header" style="margin-bottom:24px;">
        <div>
            <h1 class="page-title">Tasks</h1>
            <p class="page-subtitle">Track and manage sprint tasks assigned to developers.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Create Task
        </a>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert-success" style="margin-bottom:18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Month Filter Bar --}}
    @if($availableMonths->count() > 0)
    <div style="margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">

            {{-- Label --}}
            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.08em; margin-right:4px; display:flex; align-items:center; gap:5px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Filter by Month
            </span>

            {{-- All months chip --}}
            <a
                href="{{ route('tasks.index') }}"
                style="
                    display:inline-flex; align-items:center; gap:4px;
                    padding:4px 12px; border-radius:99px; font-size:12px; font-weight:600;
                    text-decoration:none; transition:all 0.15s;
                    {{ !$selectedMonth
                        ? 'background:var(--accent,#6366f1); color:#fff; border:1px solid transparent; box-shadow:0 2px 8px rgba(99,102,241,0.25);'
                        : 'background:rgba(99,102,241,0.07); color:var(--text-muted); border:1px solid rgba(99,102,241,0.15);' }}"
            >
                All
            </a>

            {{-- One chip per available month --}}
            @foreach($availableMonths as $month)
                @php $label = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'); @endphp
                <a
                    href="{{ route('tasks.index', ['month' => $month]) }}"
                    style="
                        display:inline-flex; align-items:center; gap:4px;
                        padding:4px 12px; border-radius:99px; font-size:12px; font-weight:600;
                        text-decoration:none; transition:all 0.15s;
                        {{ $selectedMonth === $month
                            ? 'background:var(--accent,#6366f1); color:#fff; border:1px solid transparent; box-shadow:0 2px 8px rgba(99,102,241,0.25);'
                            : 'background:rgba(99,102,241,0.07); color:var(--text-muted); border:1px solid rgba(99,102,241,0.15);' }}"
                >
                    {{ $label }}
                </a>
            @endforeach

        </div>

        {{-- Active filter info --}}
        @if($selectedMonth)
        <div style="margin-top:10px; display:flex; align-items:center; gap:8px;">
            <span style="font-size:12px; color:var(--text-muted);">
                Showing <strong style="color:var(--text-primary);">{{ $tasks->count() }}</strong>
                {{ Str::plural('task', $tasks->count()) }} registered in
                <strong style="color:#6366f1;">{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y') }}</strong>
            </span>
            <a href="{{ route('tasks.index') }}" style="font-size:11px; color:#6366f1; text-decoration:none; display:inline-flex; align-items:center; gap:3px; font-weight:600;">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                Clear
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- Table --}}

    <div class="table-container">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Task</th>
                        <th>Assignee</th>
                        <th>Status</th>
                        <th>Est. Hours</th>
                        <th>Actual Hours</th>
                        <th>Assigned Date</th>
                        <th style="text-align:right; padding-right:24px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td style="padding-left:24px; max-width:300px;">
                                <p style="font-size:13.5px; font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $task->title }}</p>
                                <p style="font-size:11.5px; color:var(--text-muted); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:260px;">{{ $task->description ?? 'No description.' }}</p>
                            </td>
                            <td>
                                @if($task->isIndividual() && $task->employee)
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="avatar avatar-sm" style="width:26px; height:26px; font-size:10px; border-radius:6px;">
                                            {{ strtoupper(substr($task->employee->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span style="font-size:13px; color:var(--text-secondary); font-weight:500; white-space:nowrap;">{{ $task->employee->name }}</span>
                                            <div style="font-size:9.5px; color:var(--text-muted);">Individual</div>
                                        </div>
                                    </div>
                                @elseif($task->isTeam() && $task->team)
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="avatar avatar-sm" style="width:26px; height:26px; font-size:10px; border-radius:6px; background:linear-gradient(135deg,#8b5cf6,#3b82f6);">
                                            {{ strtoupper(substr($task->team->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span style="font-size:13px; color:var(--text-secondary); font-weight:500; white-space:nowrap;">{{ $task->team->name }}</span>
                                            <div style="font-size:9.5px; color:#a5b4fc; font-weight:600;">Team ({{ $task->members->count() }} members)</div>
                                        </div>
                                    </div>
                                @else
                                    <span style="font-size:12.5px; color:#334155; font-style:italic;">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if ($task->status === 'Completed')
                                    <span class="badge badge-completed"><span class="badge-dot"></span>Completed</span>
                                @elseif ($task->status === 'In Progress')
                                    <span class="badge badge-in-progress"><span class="badge-dot"></span>In Progress</span>
                                @else
                                    <span class="badge badge-pending"><span class="badge-dot"></span>Pending</span>
                                @endif
                            </td>
                            <td style="font-family:monospace; font-size:12.5px; color:#818cf8; font-weight:600;">
                                {{ number_format($task->estimated_hours, 1) }}h
                            </td>
                            <td style="font-family:monospace; font-size:12.5px; color:var(--text-secondary); font-weight:600;">
                                {{ $task->actual_hours !== null ? number_format($task->actual_hours, 1) . 'h' : '—' }}
                            </td>
                            <td style="font-size:12px; color:var(--text-muted); white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($task->assigned_date)->format('M d, Y') }}
                            </td>
                            <td style="text-align:right; padding-right:24px; white-space:nowrap;">
                                <div style="display:flex; align-items:center; gap:6px; justify-content:flex-end;">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn-ghost" style="font-size:12px; padding:5px 10px;">View</a>
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="btn-warn" style="font-size:12px; padding:5px 10px;">Edit</a>
                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this task? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="font-size:12px; padding:5px 10px; cursor:pointer; font-family:inherit;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:60px 24px;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                    <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15); display:flex; align-items:center; justify-content:center;">
                                        <svg width="22" height="22" fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/></svg>
                                    </div>
                                    <div>
                                        <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No tasks found</p>
                                        <p style="font-size:12.5px; color:#334155; margin-top:4px;">Click "Create Task" to assign the first sprint task.</p>
                                    </div>
                                    <a href="{{ route('tasks.create') }}" class="btn-primary" style="margin-top:6px; font-size:13px;">Create Task</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
