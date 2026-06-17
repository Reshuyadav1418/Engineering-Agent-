@extends('layouts.app')

@section('content')
<div class="page-container animate-fade-up" style="max-width:1200px;">

    {{-- Header --}}
    <div class="section-header" style="margin-bottom:24px;">
        <div>
            <h1 class="page-title">Teams</h1>
            <p class="page-subtitle">Manage engineering squads, track collective performance, and task metrics.</p>
        </div>
        <a href="{{ route('teams.create') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Create Team
        </a>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert-success" style="margin-bottom:18px;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="flex-shrink:0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="table-container">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="padding-left:24px;">Team Name</th>
                        <th>Team Lead</th>
                        <th>Members</th>
                        <th>Active Tasks</th>
                        <th>Completed Tasks</th>
                        <th>Team Productivity</th>
                        <th>Team Score</th>
                        <th style="text-align:right; padding-right:24px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teams as $team)
                        <tr>
                            <td style="padding-left:24px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar avatar-sm" style="background:linear-gradient(135deg, #8b5cf6, #3b82f6);">
                                        {{ strtoupper(substr($team->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p style="font-size:14px; font-weight:700; color:var(--text-primary); white-space:nowrap;">{{ $team->name }}</p>
                                        <p style="font-size:12px; color:var(--text-muted); font-weight:400;" class="truncate-2">{{ $team->description ?? 'No description.' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($team->lead)
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div class="avatar avatar-sm" style="width:24px; height:24px; font-size:9.5px; background: #6366f1;">
                                            {{ strtoupper(substr($team->lead->name, 0, 2)) }}
                                        </div>
                                        <span style="font-size:13px; color:var(--text-secondary); font-weight:500;">{{ $team->lead->name }}</span>
                                    </div>
                                @else
                                    <span style="color:var(--text-muted); font-size:12.5px; font-style:italic;">None Assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="dept-tag">{{ $team->members->count() }} members</span>
                            </td>
                            <td style="color:var(--text-secondary); font-size:13.5px; font-weight:600;">
                                {{ $team->active_tasks_count }}
                            </td>
                            <td style="color:var(--text-secondary); font-size:13.5px; font-weight:600;">
                                {{ $team->completed_tasks_count }}
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:13.5px; font-weight:700; color:#10b981;">{{ number_format($team->productivity_score, 1) }}</span>
                                    <div style="width:50px; background:rgba(255,255,255,0.06); height:4px; border-radius:99px; overflow:hidden;">
                                        <div style="width:{{ min(100, $team->productivity_score * 10) }}%; background:#10b981; height:100%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:13.5px; font-weight:700; color:#8b5cf6;">{{ number_format($team->team_score, 1) }}</span>
                                    <div style="width:50px; background:rgba(255,255,255,0.06); height:4px; border-radius:99px; overflow:hidden;">
                                        <div style="width:{{ min(100, $team->team_score * 10) }}%; background:#8b5cf6; height:100%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:right; padding-right:24px; white-space:nowrap;">
                                <div style="display:flex; align-items:center; gap:6px; justify-content:flex-end;">
                                    <a href="{{ route('teams.show', $team) }}" class="btn-ghost" style="font-size:12px; padding:5px 10px;">View</a>
                                    <a href="{{ route('teams.edit', $team) }}" class="btn-warn" style="font-size:12px; padding:5px 10px;">Edit</a>
                                    <form action="{{ route('teams.destroy', $team) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this team? All team configurations will be removed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" style="font-size:12px; padding:5px 10px; cursor:pointer; font-family:inherit;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:60px 24px;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                    <div style="width:48px; height:48px; border-radius:12px; background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15); display:flex; align-items:center; justify-content:center;">
                                        <svg width="22" height="22" fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p style="font-size:14px; font-weight:700; color:var(--text-muted);">No teams registered</p>
                                        <p style="font-size:12.5px; color:#334155; margin-top:4px;">Click "Create Team" above to setup your first engineering team.</p>
                                    </div>
                                    <a href="{{ route('teams.create') }}" class="btn-primary" style="margin-top:6px; font-size:13px;">Create Team</a>
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
